@extends('layouts.main')
@section('title', __('Units'))
@push('styles')
	<link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
@endpush
@section('content')
	@php($usage = $planUsage ?? ['unit_count' => 0, 'unit_limit' => null, 'at_limit' => false])
	@if ($usage['unit_limit'] !== null)
		<div class="alert {{ $usage['at_limit'] ? 'alert-warning' : 'alert-info' }} d-flex flex-wrap align-items-center justify-content-between gap-3 mb-6">
			<span>
				{{ __('Plan usage: :count of :limit units', ['count' => $usage['unit_count'], 'limit' => $usage['unit_limit']]) }}
				@if ($usage['plan_name'])
					<span class="text-muted">({{ $usage['plan_name'] }})</span>
				@endif
			</span>
			@if ($usage['at_limit'])
				<a href="{{ route('billing.plans') }}" class="btn btn-sm btn-primary">{{ __('Upgrade plan') }}</a>
			@endif
		</div>
	@endif
	<div class="card">
		<div class="card-header border-0 pt-6">
			<div class="card-title"><h2 class="fw-bold m-0">{{ __('Units') }}</h2></div>
			<div class="card-toolbar">
				@if ($usage['at_limit'] ?? false)
					<a href="{{ route('billing.plans') }}" class="btn btn-light-warning">{{ __('Upgrade to add units') }}</a>
				@else
					<a href="{{ route('rental.units.create') }}" class="btn btn-primary">{{ __('Add unit') }}</a>
				@endif
			</div>
		</div>
		<div class="card-body py-4">
			<table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_rental_units_table">
				<thead>
					<tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
						<th class="min-w-50px">{{ __('ID') }}</th>
						<th class="min-w-150px">{{ __('Property') }}</th>
						<th class="min-w-120px">{{ __('Label') }}</th>
						<th class="min-w-80px">{{ __('Beds') }}</th>
						<th class="min-w-100px">{{ __('Rent') }}</th>
						<th class="min-w-110px">{{ __('Occupancy') }}</th>
						<th class="min-w-100px">{{ __('Status') }}</th>
						<th class="min-w-125px">{{ __('Created') }}</th>
						<th class="text-end min-w-150px">{{ __('Actions') }}</th>
					</tr>
				</thead>
				<tbody class="text-gray-600 fw-semibold"></tbody>
			</table>
		</div>
	</div>
@endsection
@push('scripts')
	<script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
			const table = $('#kt_rental_units_table').DataTable({
				processing: true, serverSide: true,
				ajax: { url: @json(route('rental.units.data')), type: 'GET' },
				pageLength: 10, order: [[0, 'desc']],
				columns: [
					{ data: 'id', name: 'id' },
					{ data: 'property', name: 'property', orderable: false, searchable: false },
					{ data: 'label', name: 'label' },
					{ data: 'bedrooms', name: 'bedrooms' },
					{ data: 'monthly_rent', name: 'monthly_rent' },
					{ data: 'occupancy_status', name: 'occupancy_status', orderable: false, searchable: false },
					{ data: 'is_active', name: 'is_active', orderable: false, searchable: false },
					{ data: 'created_at', name: 'created_at' },
					{ data: 'actions', orderable: false, searchable: false, className: 'text-end' },
				],
			});
			$('#kt_rental_units_table').on('click', '.js-delete-row', function () {
				const btn = $(this);
				Swal.fire({ title: @json(__('Delete?')), text: btn.data('name'), icon: 'warning', showCancelButton: true, confirmButtonText: @json(__('Yes, delete')), cancelButtonText: @json(__('Cancel')) }).then(function (r) {
					if (!r.isConfirmed) return;
					fetch(btn.data('url'), { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
						.then(function (res) { return res.json().then(function (body) { if (!res.ok) throw new Error(body.message || res.statusText); }); })
						.then(function () { Swal.fire({ icon: 'success', title: @json(__('Deleted')), timer: 1500, showConfirmButton: false }); table.ajax.reload(null, false); })
						.catch(function (e) { Swal.fire({ icon: 'error', title: @json(__('Error')), text: e.message }); });
				});
			});
		});
	</script>
@endpush
