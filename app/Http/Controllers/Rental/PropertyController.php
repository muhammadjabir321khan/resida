<?php

namespace App\Http\Controllers\Rental;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rental\StorePropertyRequest;
use App\Http\Requests\Rental\UpdatePropertyRequest;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PropertyController extends Controller
{
    private const ORDERABLE = ['id', 'name', 'city', 'property_type', 'is_active', 'created_at'];

    public function index(): View
    {
        return view('rental.properties.index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = Property::query();

        $search = trim((string) $request->input('search.value', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('address_line_1', 'like', "%{$search}%");
            });
        }

        $recordsTotal = Property::query()->count();
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

        $data = $rows->map(function (Property $property): array {
            $active = $property->is_active
                ? '<span class="badge badge-light-success">'.e(__('Active')).'</span>'
                : '<span class="badge badge-light-secondary">'.e(__('Inactive')).'</span>';
            $editUrl = route('rental.properties.edit', $property);
            $deleteUrl = route('rental.properties.destroy', $property);
            $actions = '<div class="d-flex flex-shrink-0 gap-1">'
                .'<a href="'.e($editUrl).'" class="btn btn-sm btn-light-primary">'.e(__('Edit')).'</a>'
                .'<button type="button" class="btn btn-sm btn-light-danger js-delete-row" data-url="'.e($deleteUrl).'" data-name="'.e($property->name).'">'.e(__('Delete')).'</button>'
                .'</div>';

            return [
                'id' => $property->id,
                'name' => e($property->name),
                'city' => e($property->city ?? '—'),
                'property_type' => e($property->property_type),
                'is_active' => $active,
                'created_at' => $property->created_at?->format('Y-m-d H:i') ?? '—',
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
        return view('rental.properties.create');
    }

    public function store(StorePropertyRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['photo']);
        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('properties', 'public');
        }
        Property::create($data);

        return redirect()->route('rental.properties.index')->with('swal', [
            'icon' => 'success',
            'title' => __('Saved'),
            'text' => __('Property created.'),
        ]);
    }

    public function edit(Property $property): View
    {
        return view('rental.properties.edit', compact('property'));
    }

    public function update(UpdatePropertyRequest $request, Property $property): RedirectResponse
    {
        $data = $request->safe()->except(['photo']);
        if ($request->hasFile('photo')) {
            if ($property->photo_path) {
                Storage::disk('public')->delete($property->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('properties', 'public');
        }
        $property->update($data);

        return redirect()->route('rental.properties.index')->with('swal', [
            'icon' => 'success',
            'title' => __('Saved'),
            'text' => __('Property updated.'),
        ]);
    }

    public function destroy(Property $property): JsonResponse|RedirectResponse
    {
        $property->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => __('Deleted.')]);
        }

        return redirect()->route('rental.properties.index')->with('swal', [
            'icon' => 'success',
            'title' => __('Deleted'),
            'text' => __('Property removed.'),
        ]);
    }
}
