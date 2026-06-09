@extends('layouts.main')
@section('title', __('Rent payments'))
@push('styles')
	<link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
@endpush
@section('content')
	<div class="card">
		<div class="card-header border-0 pt-6">
			<div class="card-title"><h2 class="fw-bold m-0">{{ __('Rent payments') }}</h2></div>
			<div class="card-toolbar">
				<span class="text-muted fs-7">{{ __('Generated from active leases. Record payments here.') }}</span>
			</div>
		</div>
		<div class="card-body py-4">
			<table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_rental_payments_table">
				<thead>
					<tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
						<th class="min-w-50px">{{ __('ID') }}</th>
						<th class="min-w-150px">{{ __('Lease') }}</th>
						<th class="min-w-125px">{{ __('Tenant') }}</th>
						<th class="min-w-100px">{{ __('Due') }}</th>
						<th class="min-w-90px">{{ __('Due amt') }}</th>
						<th class="min-w-90px">{{ __('Paid') }}</th>
						<th class="min-w-100px">{{ __('Status') }}</th>
						<th class="min-w-125px">{{ __('Created') }}</th>
						<th class="text-end min-w-180px">{{ __('Actions') }}</th>
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
			const table = $('#kt_rental_payments_table').DataTable({
				processing: true, serverSide: true,
				ajax: { url: @json(route('rental.payments.data')), type: 'GET' },
				pageLength: 10, order: [[3, 'asc']],
				columns: [
					{ data: 'id', name: 'id' },
					{ data: 'lease', name: 'lease', orderable: false, searchable: false },
					{ data: 'tenant', name: 'tenant', orderable: false, searchable: false },
					{ data: 'due_date', name: 'due_date' },
					{ data: 'amount_due', name: 'amount_due' },
					{ data: 'amount_paid', name: 'amount_paid' },
					{ data: 'status', name: 'status', orderable: false, searchable: false },
					{ data: 'created_at', name: 'created_at' },
					{ data: 'actions', orderable: false, searchable: false, className: 'text-end' },
				],
			});
			$('#kt_rental_payments_table').on('click', '.js-delete-row', function () {
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
