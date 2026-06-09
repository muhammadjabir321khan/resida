<?php

namespace App\Http\Controllers\Rental;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rental\ReportExportRequest;
use App\Models\Property;
use App\Services\LandlordReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        private readonly LandlordReportService $reports,
    ) {}

    public function index(Request $request): View
    {
        $properties = Property::query()->orderBy('name')->get(['id', 'name']);
        $types = LandlordReportService::types();
        $type = (string) $request->query('type', LandlordReportService::TYPE_RENT_ROLL);
        if (! in_array($type, $types, true)) {
            $type = LandlordReportService::TYPE_RENT_ROLL;
        }

        $dateFrom = $this->parseDate($request->query('date_from')) ?? now()->startOfMonth();
        $dateTo = $this->parseDate($request->query('date_to')) ?? now()->endOfMonth();
        $propertyId = $request->filled('property_id') ? (int) $request->query('property_id') : null;
        if ($propertyId !== null && ! Property::query()->whereKey($propertyId)->exists()) {
            $propertyId = null;
        }

        $preview = $this->reports->build($type, $dateFrom, $dateTo, $propertyId);
        $meta = $this->reports->metaForView();

        return view('rental.reports.index', compact(
            'properties',
            'types',
            'type',
            'dateFrom',
            'dateTo',
            'propertyId',
            'preview',
            'meta',
        ));
    }

    public function export(ReportExportRequest $request): Response|StreamedResponse
    {
        $v = $request->validated();
        $type = $v['type'];
        $dateFrom = isset($v['date_from']) ? Carbon::parse($v['date_from']) : null;
        $dateTo = isset($v['date_to']) ? Carbon::parse($v['date_to']) : null;
        $propertyId = isset($v['property_id']) ? (int) $v['property_id'] : null;

        if ($this->reports->usesDateRange($type)) {
            $dateFrom = $dateFrom ?? now()->startOfMonth();
            $dateTo = $dateTo ?? now()->endOfMonth();
        }

        $report = $this->reports->build($type, $dateFrom, $dateTo, $propertyId);
        $meta = $this->reports->metaForView();
        $slug = str_replace('_', '-', $type).'-'.now()->format('Y-m-d-His');
        $user = $request->user();

        if ($v['format'] === 'csv') {
            $body = $this->reports->toCsvLines($report)->implode("\n");
            $utf8Bom = "\xEF\xBB\xBF";

            return response($utf8Bom.$body, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$slug.'.csv"',
            ]);
        }

        $pdf = Pdf::loadView('rental.reports.pdf.document', [
            'report' => $report,
            'meta' => $meta,
            'generatedAt' => now(),
            'generatedBy' => $user?->name ?? $user?->email ?? '',
        ])->setPaper('a4', 'landscape');

        return $pdf->download($slug.'.pdf');
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse((string) $value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}
