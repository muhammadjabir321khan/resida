<?php

namespace App\Services;

use App\Models\Lease;
use App\Models\Property;
use App\Models\RentPaymentInstallment;
use App\Models\RentalTenant;
use App\Models\RentalTransaction;
use App\Models\UserSetting;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LandlordReportService
{
    public const TYPE_RENT_ROLL = 'rent_roll';

    public const TYPE_RENT_COLLECTIONS = 'rent_collections';

    public const TYPE_TENANT_DIRECTORY = 'tenant_directory';

    public const TYPE_INCOME_EXPENSE = 'income_expense';

    public const TYPE_PROPERTY_PORTFOLIO = 'property_portfolio';

    /** @return list<string> */
    public static function types(): array
    {
        return [
            self::TYPE_RENT_ROLL,
            self::TYPE_RENT_COLLECTIONS,
            self::TYPE_TENANT_DIRECTORY,
            self::TYPE_INCOME_EXPENSE,
            self::TYPE_PROPERTY_PORTFOLIO,
        ];
    }

    /**
     * @return array{title: string, subtitle: string, headers: list<string>, rows: list<list<string>>, foot: ?list<string>}
     */
    public function build(
        string $type,
        ?Carbon $dateFrom,
        ?Carbon $dateTo,
        ?int $propertyId,
    ): array {
        $meta = $this->filterSubtitle($type, $dateFrom, $dateTo, $propertyId);

        return match ($type) {
            self::TYPE_RENT_ROLL => $this->rentRoll($propertyId, $meta),
            self::TYPE_RENT_COLLECTIONS => $this->rentCollections($dateFrom, $dateTo, $propertyId, $meta),
            self::TYPE_TENANT_DIRECTORY => $this->tenantDirectory($meta),
            self::TYPE_INCOME_EXPENSE => $this->incomeExpense($dateFrom, $dateTo, $propertyId, $meta),
            self::TYPE_PROPERTY_PORTFOLIO => $this->propertyPortfolio($meta),
            default => [
                'title' => __('Report'),
                'subtitle' => $meta,
                'headers' => [],
                'rows' => [],
                'foot' => null,
            ],
        };
    }

    public function reportTitle(string $type): string
    {
        return match ($type) {
            self::TYPE_RENT_ROLL => __('Rent roll'),
            self::TYPE_RENT_COLLECTIONS => __('Rent collections'),
            self::TYPE_TENANT_DIRECTORY => __('Tenant directory'),
            self::TYPE_INCOME_EXPENSE => __('Income & expenses'),
            self::TYPE_PROPERTY_PORTFOLIO => __('Property portfolio'),
            default => __('Report'),
        };
    }

    public function usesDateRange(string $type): bool
    {
        return in_array($type, [self::TYPE_RENT_COLLECTIONS, self::TYPE_INCOME_EXPENSE], true);
    }

    public function metaForView(): array
    {
        return [
            'currency' => UserSetting::getValue('default_currency', 'USD') ?? 'USD',
            'company' => UserSetting::getValue('company_name', '') ?? '',
            'date_format' => UserSetting::getValue('date_format', 'Y-m-d') ?? 'Y-m-d',
        ];
    }

    private function filterSubtitle(
        string $type,
        ?Carbon $dateFrom,
        ?Carbon $dateTo,
        ?int $propertyId,
    ): string {
        $parts = [];
        if ($this->usesDateRange($type) && $dateFrom && $dateTo) {
            $parts[] = $dateFrom->toDateString().' — '.$dateTo->toDateString();
        }
        if ($propertyId) {
            $name = Property::query()->whereKey($propertyId)->value('name');
            $parts[] = $name ? (string) $name : __('Property #').$propertyId;
        }

        return implode(' · ', $parts);
    }

    /**
     * @param  array{title: string, subtitle: string, headers: list<string>, rows: list<list<string>>, foot: ?list<string>}  $base
     * @return array{title: string, subtitle: string, headers: list<string>, rows: list<list<string>>, foot: ?list<string>}
     */
    private function withTitle(string $title, string $subtitle, array $base): array
    {
        $base['title'] = $title;
        $base['subtitle'] = $subtitle;

        return $base;
    }

    private function rentRoll(?int $propertyId, string $subtitle): array
    {
        $q = Lease::query()
            ->with(['property', 'unit', 'tenant'])
            ->orderBy('property_id')
            ->orderBy('id');
        if ($propertyId) {
            $q->where('property_id', $propertyId);
        }
        $leases = $q->get();

        $headers = [__('Property'), __('Unit'), __('Tenant'), __('Start'), __('End'), __('Rent'), __('Status')];
        $rows = $leases->map(function (Lease $l): array {
            $df = UserSetting::getValue('date_format', 'Y-m-d') ?? 'Y-m-d';

            return [
                $l->property?->name ?? '—',
                $l->unit?->label ?? '—',
                $l->tenant?->full_name ?? '—',
                $l->start_date?->format($df) ?? '—',
                $l->end_date?->format($df) ?? '—',
                number_format((float) $l->monthly_rent, 2),
                (string) $l->status,
            ];
        })->values()->all();

        return $this->withTitle(__('Rent roll'), $subtitle, [
            'headers' => $headers,
            'rows' => $rows,
            'foot' => [__('Leases').': '.count($rows)],
        ]);
    }

    private function rentCollections(?Carbon $from, ?Carbon $to, ?int $propertyId, string $subtitle): array
    {
        $from = $from ?? now()->startOfMonth();
        $to = $to ?? now()->endOfMonth();

        $q = RentPaymentInstallment::query()
            ->with(['lease.property', 'lease.tenant'])
            ->whereBetween('due_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('due_date')
            ->orderBy('id');
        if ($propertyId) {
            $q->whereHas('lease', fn ($lq) => $lq->where('property_id', $propertyId));
        }
        $items = $q->get();

        $headers = [__('Due'), __('Property'), __('Tenant'), __('Due amt'), __('Paid'), __('Status')];
        $sumDue = 0.0;
        $sumPaid = 0.0;
        $df = UserSetting::getValue('date_format', 'Y-m-d') ?? 'Y-m-d';
        $rows = $items->map(function (RentPaymentInstallment $i) use (&$sumDue, &$sumPaid, $df): array {
            $sumDue += (float) $i->amount_due;
            $sumPaid += (float) $i->amount_paid;

            return [
                $i->due_date?->format($df) ?? '—',
                $i->lease?->property?->name ?? '—',
                $i->lease?->tenant?->full_name ?? '—',
                number_format((float) $i->amount_due, 2),
                number_format((float) $i->amount_paid, 2),
                $i->displayStatus(),
            ];
        })->values()->all();

        return $this->withTitle(__('Rent collections'), $subtitle, [
            'headers' => $headers,
            'rows' => $rows,
            'foot' => [
                __('Installments').': '.count($rows),
                __('Total due').': '.number_format($sumDue, 2),
                __('Total paid').': '.number_format($sumPaid, 2),
            ],
        ]);
    }

    private function tenantDirectory(string $subtitle): array
    {
        $tenants = RentalTenant::query()
            ->withCount('leases')
            ->orderBy('full_name')
            ->get();

        $headers = [__('Name'), __('Email'), __('Phone'), __('Leases')];
        $rows = $tenants->map(fn (RentalTenant $t): array => [
            $t->full_name,
            $t->email ?? '—',
            $t->phone ?? '—',
            (string) ($t->leases_count ?? 0),
        ])->values()->all();

        return $this->withTitle(__('Tenant directory'), $subtitle, [
            'headers' => $headers,
            'rows' => $rows,
            'foot' => [__('Tenants').': '.count($rows)],
        ]);
    }

    private function incomeExpense(?Carbon $from, ?Carbon $to, ?int $propertyId, string $subtitle): array
    {
        $from = $from ?? now()->startOfMonth();
        $to = $to ?? now()->endOfMonth();

        $q = RentalTransaction::query()
            ->with(['property'])
            ->whereBetween('transaction_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('transaction_date')
            ->orderBy('id');
        if ($propertyId) {
            $q->where('property_id', $propertyId);
        }
        $tx = $q->get();

        $headers = [__('Date'), __('Direction'), __('Category'), __('Amount'), __('Property'), __('Description')];
        $income = 0.0;
        $expense = 0.0;
        $df = UserSetting::getValue('date_format', 'Y-m-d') ?? 'Y-m-d';
        $rows = $tx->map(function (RentalTransaction $r) use (&$income, &$expense, $df): array {
            $amt = (float) $r->amount;
            if ($r->direction === RentalTransaction::DIRECTION_INCOME) {
                $income += $amt;
            } else {
                $expense += $amt;
            }

            return [
                $r->transaction_date?->format($df) ?? '—',
                $r->direction,
                $r->category ?? '—',
                number_format($amt, 2),
                $r->property?->name ?? '—',
                \Illuminate\Support\Str::limit((string) ($r->description ?? ''), 60),
            ];
        })->values()->all();

        return $this->withTitle(__('Income & expenses'), $subtitle, [
            'headers' => $headers,
            'rows' => $rows,
            'foot' => [
                __('Lines').': '.count($rows),
                __('Income').': '.number_format($income, 2),
                __('Expenses').': '.number_format($expense, 2),
                __('Net').': '.number_format($income - $expense, 2),
            ],
        ]);
    }

    private function propertyPortfolio(string $subtitle): array
    {
        $props = Property::query()
            ->withCount(['rentalUnits', 'leases'])
            ->orderBy('name')
            ->get();

        $headers = [__('Property'), __('City'), __('Units'), __('Leases'), __('Active')];
        $rows = $props->map(fn (Property $p): array => [
            $p->name,
            $p->city ?? '—',
            (string) ($p->rental_units_count ?? 0),
            (string) ($p->leases_count ?? 0),
            $p->is_active ? __('Yes') : __('No'),
        ])->values()->all();

        return $this->withTitle(__('Property portfolio'), $subtitle, [
            'headers' => $headers,
            'rows' => $rows,
            'foot' => [__('Properties').': '.count($rows)],
        ]);
    }

    /**
     * @param  array{title: string, headers: list<string>, rows: list<list<string>>, foot: ?list<string>}  $report
     */
    public function toCsvLines(array $report): Collection
    {
        $lines = collect();
        $lines->push($report['title']);
        if (($report['subtitle'] ?? '') !== '') {
            $lines->push($report['subtitle']);
        }
        $lines->push('');
        $lines->push($this->csvRow($report['headers']));
        foreach ($report['rows'] as $row) {
            $lines->push($this->csvRow($row));
        }
        if (! empty($report['foot'])) {
            $lines->push('');
            foreach ($report['foot'] as $f) {
                $lines->push($this->csvRow([$f]));
            }
        }

        return $lines;
    }

    /**
     * @param  list<string>  $cells
     */
    private function csvRow(array $cells): string
    {
        $out = fopen('php://temp', 'r+');
        if ($out === false) {
            return implode(',', $cells);
        }
        fputcsv($out, $cells);
        rewind($out);
        $line = stream_get_contents($out) ?: '';
        fclose($out);

        return rtrim($line, "\r\n");
    }
}
