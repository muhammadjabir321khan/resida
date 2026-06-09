@extends('layouts.main')
@section('title', __('Tenants'))
@push('styles')
	<link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
@endpush
@section('content')
	<div class="card">
		<div class="card-header border-0 pt-6">
			<div class="card-title"><h2 class="fw-bold m-0">{{ __('Tenants') }}</h2></div>
			<div class="card-toolbar">
				<a href="{{ route('rental.tenants.create') }}" class="btn btn-primary">{{ __('Add tenant') }}</a>
			</div>
		</div>
		<div class="card-body py-4">
			<table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_rental_tenants_table">
				<thead>
					<tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
						<th class="min-w-50px">{{ __('ID') }}</th>
						<th class="min-w-175px">{{ __('Name') }}</th>
						<th class="min-w-175px">{{ __('Email') }}</th>
						<th class="min-w-125px">{{ __('Phone') }}</th>
						<th class="min-w-125px">{{ __('Portal') }}</th>
						<th class="min-w-125px">{{ __('Created') }}</th>
						<th class="text-end min-w-200px">{{ __('Actions') }}</th>
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
			const table = $('#kt_rental_tenants_table').DataTable({
				processing: true, serverSide: true,
				ajax: { url: @json(route('rental.tenants.data')), type: 'GET' },
				pageLength: 10, order: [[0, 'desc']],
				columns: [
					{ data: 'id', name: 'id' },
					{ data: 'full_name', name: 'full_name' },
					{ data: 'email', name: 'email' },
					{ data: 'phone', name: 'phone' },
					{ data: 'portal_status', orderable: false, searchable: false },
					{ data: 'created_at', name: 'created_at' },
					{ data: 'actions', orderable: false, searchable: false, className: 'text-end' },
				],
			});
			$('#kt_rental_tenants_table').on('click', '.js-send-invite', function () {
				const btn = $(this);
				Swal.fire({ title: @json(__('Send portal invite?')), text: btn.data('name'), icon: 'question', showCancelButton: true, confirmButtonText: @json(__('Send invite')), cancelButtonText: @json(__('Cancel')) }).then(function (r) {
					if (!r.isConfirmed) return;
					fetch(btn.data('url'), { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
						.then(function (res) { return res.json().then(function (body) { if (!res.ok) throw new Error(body.message || res.statusText); return body; }); })
						.then(function () { Swal.fire({ icon: 'success', title: @json(__('Invite sent')), timer: 1500, showConfirmButton: false }); table.ajax.reload(null, false); })
						.catch(function (e) { Swal.fire({ icon: 'error', title: @json(__('Error')), text: e.message }); });
				});
			});
			$('#kt_rental_tenants_table').on('click', '.js-delete-row', function () {
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
