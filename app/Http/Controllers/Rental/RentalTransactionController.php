<?php

namespace App\Http\Controllers\Rental;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rental\StoreRentalTransactionRequest;
use App\Http\Requests\Rental\UpdateRentalTransactionRequest;
use App\Models\Lease;
use App\Models\Property;
use App\Models\RentalTransaction;
use App\Models\RentalUnit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RentalTransactionController extends Controller
{
    private const ORDERABLE = ['id', 'direction', 'category', 'amount', 'transaction_date', 'created_at'];

    public function index(): View
    {
        return view('rental.transactions.index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = RentalTransaction::query()->with(['property', 'lease']);

        $search = trim((string) $request->input('search.value', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('category', 'like', "%{$search}%")
                    ->orWhere('direction', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%")
                    ->orWhereHas('property', fn ($p) => $p->where('name', 'like', "%{$search}%"));
            });
        }

        $recordsTotal = RentalTransaction::query()->count();
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

        $data = $rows->map(function (RentalTransaction $row): array {
            $editUrl = route('rental.transactions.edit', $row);
            $deleteUrl = route('rental.transactions.destroy', $row);
            $actions = '<div class="d-flex flex-shrink-0 gap-1">'
                .'<a href="'.e($editUrl).'" class="btn btn-sm btn-light-primary">'.e(__('Edit')).'</a>'
                .'<button type="button" class="btn btn-sm btn-light-danger js-delete-row" data-url="'.e($deleteUrl).'" data-name="'.e($row->category).'">'.e(__('Delete')).'</button>'
                .'</div>';
            $dirBadge = $row->direction === RentalTransaction::DIRECTION_INCOME
                ? '<span class="badge badge-light-success">'.e(__('Income')).'</span>'
                : '<span class="badge badge-light-danger">'.e(__('Expense')).'</span>';

            return [
                'id' => $row->id,
                'direction' => $dirBadge,
                'category' => e($row->category),
                'amount' => e(number_format((float) $row->amount, 2)),
                'transaction_date' => $row->transaction_date?->format('Y-m-d') ?? '—',
                'property' => e($row->property?->name ?? '—'),
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

    public function create(): View
    {
        $properties = Property::orderBy('name')->get();
        $leases = Lease::query()->with('property')->orderByDesc('id')->limit(200)->get();
        $units = RentalUnit::query()->with('property')->orderBy('property_id')->orderBy('label')->get();

        return view('rental.transactions.create', compact('properties', 'leases', 'units'));
    }

    public function store(StoreRentalTransactionRequest $request): RedirectResponse
    {
        RentalTransaction::create($request->validated());

        return redirect()->route('rental.transactions.index')->with('swal', [
            'icon' => 'success',
            'title' => __('Saved'),
            'text' => __('Transaction recorded.'),
        ]);
    }

    public function edit(RentalTransaction $transaction): View
    {
        $properties = Property::orderBy('name')->get();
        $leases = Lease::query()->with('property')->orderByDesc('id')->limit(200)->get();
        $units = RentalUnit::query()->with('property')->orderBy('property_id')->orderBy('label')->get();

        return view('rental.transactions.edit', compact('transaction', 'properties', 'leases', 'units'));
    }

    public function update(UpdateRentalTransactionRequest $request, RentalTransaction $transaction): RedirectResponse
    {
        $transaction->update($request->validated());

        return redirect()->route('rental.transactions.index')->with('swal', [
            'icon' => 'success',
            'title' => __('Saved'),
            'text' => __('Transaction updated.'),
        ]);
    }

    public function destroy(RentalTransaction $transaction): JsonResponse|RedirectResponse
    {
        $transaction->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => __('Deleted.')]);
        }

        return redirect()->route('rental.transactions.index')->with('swal', [
            'icon' => 'success',
            'title' => __('Deleted'),
            'text' => __('Transaction removed.'),
        ]);
    }
}
