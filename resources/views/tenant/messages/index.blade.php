@extends('layouts.main')
@section('title', __('Messages'))
@section('content')
	<div class="card">
		<div class="card-header border-0 pt-6">
			<div class="card-title"><h2 class="fw-bold m-0">{{ __('Messages') }}</h2></div>
		</div>
		<div class="card-body py-4">
			<div class="table-responsive">
				<table class="table align-middle table-row-dashed gy-4">
					<thead>
						<tr class="text-muted fw-bold fs-7 text-uppercase">
							<th>{{ __('Conversation') }}</th>
							<th>{{ __('Property') }}</th>
							<th>{{ __('Last activity') }}</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						@forelse ($threads as $thread)
							@php($unread = $thread->unreadCountForTenant(auth()->id()))
							<tr>
								<td>
									<div class="fw-semibold text-gray-900">{{ $thread->displaySubject() }}</div>
									<div class="text-muted fs-8">{{ $thread->latestMessage?->body ? Str::limit($thread->latestMessage->body, 80) : '—' }}</div>
								</td>
								<td>{{ $thread->lease?->property?->name ?? '—' }}</td>
								<td>{{ $thread->last_message_at?->diffForHumans() ?? '—' }}</td>
								<td class="text-end">
									@if ($unread > 0)
										<span class="badge badge-light-danger me-2">{{ $unread }}</span>
									@endif
									<a href="{{ route('tenant.messages.show', $thread) }}" class="btn btn-sm btn-light-primary">{{ __('Open') }}</a>
								</td>
							</tr>
						@empty
							<tr><td colspan="4" class="text-muted">{{ __('No conversations yet.') }}</td></tr>
						@endforelse
					</tbody>
				</table>
			</div>
			{{ $threads->links() }}
		</div>
	</div>
@endsection
