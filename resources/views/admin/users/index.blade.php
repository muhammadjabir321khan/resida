@extends('layouts.main')

@section('title', __('Users'))

@push('styles')
	<link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
	<div class="card">
		<div class="card-header border-0 pt-6">
			<div class="card-title">
				<h2 class="fw-bold m-0">{{ __('Users') }}</h2>
			</div>
			<div class="card-toolbar">
				<a href="{{ route('admin.users.create') }}" class="btn btn-primary">{{ __('Add user') }}</a>
			</div>
		</div>
		<div class="card-body py-4">
			<table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_admin_users_table">
				<thead>
					<tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
						<th class="min-w-50px">{{ __('ID') }}</th>
						<th class="min-w-150px">{{ __('Name') }}</th>
						<th class="min-w-200px">{{ __('Email') }}</th>
						<th class="min-w-200px">{{ __('Roles') }}</th>
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
			const table = $('#kt_admin_users_table').DataTable({
				processing: true,
				serverSide: true,
				ajax: {
					url: @json(route('admin.users.data')),
					type: 'GET',
				},
				pageLength: 10,
				lengthMenu: [10, 25, 50, 100],
				order: [[0, 'desc']],
				columns: [
					{ data: 'id', name: 'id' },
					{ data: 'name', name: 'name' },
					{ data: 'email', name: 'email' },
					{ data: 'roles', name: 'roles', orderable: false, searchable: false },
					{ data: 'created_at', name: 'created_at' },
					{ data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' },
				],
			});

			$('#kt_admin_users_table').on('click', '.js-delete-user', function () {
				const btn = $(this);
				const url = btn.data('url');
				const name = btn.data('name');
				Swal.fire({
					title: @json(__('Delete user?')),
					text: name,
					icon: 'warning',
					showCancelButton: true,
					confirmButtonText: @json(__('Yes, delete')),
					cancelButtonText: @json(__('Cancel')),
				}).then(function (result) {
					if (!result.isConfirmed) return;
					fetch(url, {
						method: 'DELETE',
						headers: {
							'X-CSRF-TOKEN': csrf,
							'Accept': 'application/json',
							'X-Requested-With': 'XMLHttpRequest',
						},
					})
						.then(function (res) {
							return res.json().then(function (body) {
								if (!res.ok) throw new Error(body.message || res.statusText);
								return body;
							});
						})
						.then(function () {
							Swal.fire({ icon: 'success', title: @json(__('Deleted')), timer: 1500, showConfirmButton: false });
							table.ajax.reload(null, false);
						})
						.catch(function (err) {
							Swal.fire({ icon: 'error', title: @json(__('Error')), text: err.message });
						});
				});
			});
		});
	</script>
@endpush
