@extends('layouts.main')
@section('title', __('Activity log'))
@section('content')
	<div class="card">
		<div class="card-header border-0 pt-6">
			<div class="card-title"><h2 class="fw-bold m-0">{{ __('Activity log') }}</h2></div>
			<div class="card-toolbar">
				<span class="text-muted fs-7">{{ __('Recent changes to leases and rent installments.') }}</span>
			</div>
		</div>
		<div class="card-body py-4">
			<div class="table-responsive">
				<table class="table table-row-bordered align-middle gy-4">
					<thead>
						<tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
							<th>{{ __('When') }}</th>
							<th>{{ __('User') }}</th>
							<th>{{ __('Action') }}</th>
							<th>{{ __('Subject') }}</th>
							<th>{{ __('IP') }}</th>
						</tr>
					</thead>
					<tbody class="text-gray-700 fw-semibold">
						@forelse ($logs as $log)
							<tr>
								<td class="text-nowrap">{{ $log->created_at?->format('Y-m-d H:i') }}</td>
								<td>{{ $log->user?->email ?? '—' }}</td>
								<td>{{ $log->action }}</td>
								<td class="fs-7">
									@if ($log->subject_type)
										{{ class_basename($log->subject_type) }}#{{ $log->subject_id }}
									@else
										—
									@endif
									@if (!empty($log->properties))
										<pre class="fs-8 text-muted mb-0 mt-1" style="white-space: pre-wrap; max-width: 420px;">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
									@endif
								</td>
								<td class="fs-7">{{ $log->ip_address ?? '—' }}</td>
							</tr>
						@empty
							<tr><td colspan="5" class="text-muted">{{ __('No activity recorded yet.') }}</td></tr>
						@endforelse
					</tbody>
				</table>
			</div>
			<div class="mt-4">{{ $logs->withQueryString()->links() }}</div>
		</div>
	</div>
@endsection
