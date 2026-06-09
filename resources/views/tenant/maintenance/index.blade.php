@extends('layouts.main')
@section('title', __('My maintenance'))
@section('content')
	<div class="card mb-5">
		<div class="card-header border-0 pt-6">
			<div class="card-title"><h2 class="fw-bold m-0">{{ __('My maintenance requests') }}</h2></div>
			<div class="card-toolbar">
				<a href="{{ route('tenant.maintenance.create') }}" class="btn btn-primary">{{ __('Report an issue') }}</a>
			</div>
		</div>
		<div class="card-body py-4">
			<div class="table-responsive">
				<table class="table table-row-bordered align-middle gy-4">
					<thead>
						<tr class="text-muted fw-bold fs-7 text-uppercase">
							<th>{{ __('Property') }}</th>
							<th>{{ __('Title') }}</th>
							<th>{{ __('Status') }}</th>
							<th>{{ __('Priority') }}</th>
							<th>{{ __('Reported') }}</th>
						</tr>
					</thead>
					<tbody>
						@forelse ($requests as $row)
							<tr>
								<td>{{ $row->property?->name ?? '—' }}</td>
								<td>{{ $row->title }}</td>
								<td><span class="badge badge-light">{{ $row->status }}</span></td>
								<td>{{ $row->priority }}</td>
								<td>{{ $row->reported_on?->format('Y-m-d') ?? '—' }}</td>
							</tr>
						@empty
							<tr><td colspan="5" class="text-muted">{{ __('No requests yet.') }}</td></tr>
						@endforelse
					</tbody>
				</table>
			</div>
			<div class="mt-4">{{ $requests->links() }}</div>
		</div>
	</div>
@endsection
