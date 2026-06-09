@extends('layouts.main')

@section('title', __('Plans & billing'))

@section('content')
	<div class="card mb-6">
		<div class="card-header border-0 pt-6">
			<h2 class="fw-bold m-0">{{ __('Plans & billing') }}</h2>
			<p class="text-muted fs-7 mb-0 mt-1">{{ __('Subscribe to unlock the rental workspace, properties, leases, and more.') }}</p>
		</div>
		<div class="card-body pt-0">
			@if (session('subscription_required'))
				<div class="alert alert-warning">{{ session('subscription_required') }}</div>
			@endif
			@if (session('status'))
				<div class="alert alert-success">{{ session('status') }}</div>
			@endif
			@if ($errors->any())
				<div class="alert alert-danger">
					<ul class="mb-0 ps-4">
						@foreach ($errors->all() as $error)
							<li>{{ $error }}</li>
						@endforeach
					</ul>
				</div>
			@endif
			@if ($subscribed)
				<div class="alert alert-success d-flex flex-wrap align-items-center justify-content-between gap-3">
					<span>{{ __('You have an active subscription. You can use the full app.') }}</span>
					<div class="d-flex flex-wrap gap-2">
						<a href="{{ route('dashboard') }}" class="btn btn-sm btn-primary">{{ __('Go to dashboard') }}</a>
						@if (auth()->user()->stripe_id)
							<a href="{{ auth()->user()->billingPortalUrl(route('billing.plans')) }}" class="btn btn-sm btn-light">{{ __('Manage billing in Stripe') }}</a>
						@endif
					</div>
				</div>
			@elseif ($incomplete && $completePaymentUrl)
				<div class="alert alert-warning d-flex flex-wrap align-items-center justify-content-between gap-3">
					<span>{{ __('Your last payment needs to be completed before your subscription is active.') }}</span>
					<a href="{{ $completePaymentUrl }}" class="btn btn-sm btn-primary">{{ __('Complete payment') }}</a>
				</div>
			@endif
		</div>
	</div>

	<div class="row g-6">
		@foreach ($plans as $plan)
			<div class="col-md-4">
				<div class="card h-100 border border-gray-300">
					<div class="card-header border-0 pt-8 pb-0">
						<h3 class="fw-bold fs-3 mb-2">{{ __($plan['name']) }}</h3>
						<p class="text-gray-700 fs-7 mb-0">{{ __($plan['description']) }}</p>
					</div>
					<div class="card-body pt-4 d-flex flex-column">
						<div class="mb-6">
							<span class="fs-2hx fw-bold text-gray-900">{{ $plan['amount_label'] }}</span>
							<span class="text-muted fs-5">{{ $plan['interval_label'] }}</span>
						</div>
						<ul class="fs-6 text-gray-800 mb-8 ps-4 flex-grow-1">
							@foreach ($plan['features'] as $feature)
								<li class="mb-2">{{ __($feature) }}</li>
							@endforeach
						</ul>
						@if ($subscribed)
							<button type="button" class="btn btn-light w-100" disabled>{{ __('Subscribed') }}</button>
						@elseif (! $plan['configured'])
							<button type="button" class="btn btn-light w-100" disabled title="{{ __('Set the Stripe price ID in .env') }}">{{ __('Not configured') }}</button>
							<p class="text-danger fs-8 mt-2 mb-0">{{ __('Add STRIPE_PRICE_* to your environment for this plan.') }}</p>
						@else
							<form method="post" action="{{ route('billing.checkout') }}" class="d-grid">
								@csrf
								<input type="hidden" name="plan" value="{{ $plan['key'] }}" />
								<button type="submit" class="btn btn-primary w-100">{{ __('Subscribe with Stripe') }}</button>
							</form>
						@endif
					</div>
				</div>
			</div>
		@endforeach
	</div>

	<div class="card mt-8">
		<div class="card-body fs-7 text-muted">
			<p class="mb-2">{{ __('After Stripe Checkout you are redirected back here; your subscription is saved immediately so you can use the app without waiting for a webhook. For renewals and plan changes in production, still configure Stripe webhooks and run a queue worker (e.g.') }} <code>php artisan queue:work</code>).</p>
			<p class="mb-0">{{ __('Webhook URL:') }} <code>{{ url(config('cashier.path').'/webhook') }}</code></p>
		</div>
	</div>
@endsection
