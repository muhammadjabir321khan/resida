@extends('layouts.main')
@section('title', __('Conversation'))
@section('content')
	<div class="card mb-5">
		<div class="card-header border-0 pt-6 d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h2 class="fw-bold m-0">{{ $thread->displaySubject() }}</h2>
				<p class="text-muted fs-7 mb-0 mt-1">{{ $thread->lease?->property?->name ?? '—' }} · {{ $thread->rentalTenant?->full_name ?? '—' }}</p>
			</div>
			<a href="{{ route('rental.messages.index') }}" class="btn btn-sm btn-light">{{ __('All messages') }}</a>
		</div>
		<div class="card-body">
			<div class="d-flex flex-column gap-4 mb-8" style="max-height: 420px; overflow-y: auto;">
				@forelse ($thread->messages as $message)
					@php($isMine = (int) $message->sender_user_id === (int) auth()->id())
					<div class="d-flex {{ $isMine ? 'justify-content-end' : 'justify-content-start' }}">
						<div class="rounded px-4 py-3 {{ $isMine ? 'bg-light-primary' : 'bg-light' }}" style="max-width: 75%;">
							<div class="fs-8 text-muted mb-1">{{ $message->sender?->name ?? __('User') }} · {{ $message->created_at?->format('M j, Y g:i A') }}</div>
							<div class="text-gray-800" style="white-space: pre-wrap;">{{ $message->body }}</div>
						</div>
					</div>
				@empty
					<p class="text-muted mb-0">{{ __('No messages yet. Send the first one below.') }}</p>
				@endforelse
			</div>
			<form method="post" action="{{ route('rental.messages.store', $thread) }}" class="border-top pt-6">
				@csrf
				<div class="mb-4">
					<label for="body" class="form-label fw-semibold">{{ __('Reply') }}</label>
					<textarea id="body" name="body" rows="4" class="form-control form-control-solid @error('body') is-invalid @enderror" required placeholder="{{ __('Type your message…') }}">{{ old('body') }}</textarea>
					@error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
				</div>
				<button type="submit" class="btn btn-primary">{{ __('Send message') }}</button>
			</form>
		</div>
	</div>
@endsection
