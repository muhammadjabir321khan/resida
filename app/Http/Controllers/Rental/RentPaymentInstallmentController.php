<?php

namespace App\Http\Controllers\Rental;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rental\UpdateRentPaymentInstallmentRequest;
use App\Models\RentPaymentInstallment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RentPaymentInstallmentController extends Controller
{
    private const ORDERABLE = ['id', 'due_date', 'amount_due', 'amount_paid', 'status', 'created_at'];

    public function index(): View
    {
        return view('rental.payments.index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = RentPaymentInstallment::query()->with(['lease.property', 'lease.tenant']);

        $search = trim((string) $request->input('search.value', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('status', 'like', "%{$search}%")
                    ->orWhere('receipt_number', 'like', "%{$search}%")
                    ->orWhereHas('lease', function ($lq) use ($search): void {
                        $lq->whereHas('property', fn ($p) => $p->where('name', 'like', "%{$search}%"))
                            ->orWhereHas('tenant', fn ($t) => $t->where('full_name', 'like', "%{$search}%"));
                    });
            });
        }

        $recordsTotal = RentPaymentInstallment::query()->count();
        $recordsFiltered = (clone $query)->count();

        $orderColumnIndex = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderColumnName = (string) $request->input("columns.$orderColumnIndex.data", 'id');
        if (! in_array($orderColumnName, self::ORDERABLE, true)) {
            $orderColumnName = 'id';
        }
        $query->orderBy($orderColumnName, $orderDir);

        $start = max(0, (int) $request->input('start', 0));
        $length = min(max(1, (int) $request->input('length', 10)), 100);
        $rows = $query->skip($start)->take($length)->get();

        $data = $rows->map(function (RentPaymentInstallment $row): array {
            $display = $row->displayStatus();
            $badgeClass = match ($display) {
                RentPaymentInstallment::STATUS_PAID => 'success',
                RentPaymentInstallment::STATUS_OVERDUE => 'danger',
                RentPaymentInstallment::STATUS_WAIVED => 'secondary',
                default => 'warning',
            };
            $statusHtml = '<span class="badge badge-light-'.$badgeClass.'">'.e($display).'</span>';
            $dueLabel = $row->due_date?->format('Y-m-d') ?? (string) $row->id;
            $editUrl = route('rental.payments.edit', $row);
            $deleteUrl = route('rental.payments.destroy', $row);
            $actions = '<div class="d-flex flex-shrink-0 gap-1">'
                .'<a href="'.e($editUrl).'" class="btn btn-sm btn-light-primary">'.e(__('Record payment')).'</a>'
                .'<button type="button" class="btn btn-sm btn-light-danger js-delete-row" data-url="'.e($deleteUrl).'" data-name="'.e(__('Due').' '.$dueLabel).'">'.e(__('Delete')).'</button>'
                .'</div>';

            return [
                'id' => $row->id,
                'lease' => e('#'.$row->lease_id.' — '.($row->lease?->property?->name ?? '—')),
                'tenant' => e($row->lease?->tenant?->full_name ?? '—'),
                'due_date' => $row->due_date?->format('Y-m-d') ?? '—',
                'amount_due' => e(number_format((float) $row->amount_due, 2)),
                'amount_paid' => e(number_format((float) $row->amount_paid, 2)),
                'status' => $statusHtml,
                'created_at' => $row->created_at?->format('Y-m-d H:i') ?? '—',
                'actions' => $actions,
            ];
        });

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function edit(RentPaymentInstallment $payment): View
    {
        $payment->load(['lease.property', 'lease.tenant']);

        return view('rental.payments.edit', compact('payment'));
    }

    public function update(UpdateRentPaymentInstallmentRequest $request, RentPaymentInstallment $payment): RedirectResponse
    {
        $payment->update($request->validated());

        return redirect()->route('rental.payments.index')->with('swal', [
            'icon' => 'success',
            'title' => __('Saved'),
            'text' => __('Payment entry updated.'),
        ]);
    }

    public function destroy(RentPaymentInstallment $payment): JsonResponse|RedirectResponse
    {
        $payment->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => __('Deleted.')]);
        }

        return redirect()->route('rental.payments.index')->with('swal', [
            'icon' => 'success',
            'title' => __('Deleted'),
            'text' => __('Installment removed.'),
        ]);
    }
}
