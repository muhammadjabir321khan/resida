<div class="row g-5 g-xl-8 mb-5">
	<div class="col-xl-3 col-md-6">
		<div class="card card-flush h-md-100">
			<div class="card-body d-flex flex-column justify-content-between">
				<span class="fs-7 fw-semibold text-gray-500 text-uppercase">{{ __('Users') }}</span>
				<span class="fs-2hx fw-bold text-gray-800">{{ $adminStats['user_count'] }}</span>
				<a href="{{ route('admin.users.index') }}" class="fs-8 fw-semibold text-primary">{{ __('Manage') }} →</a>
			</div>
		</div>
	</div>
	<div class="col-xl-3 col-md-6">
		<div class="card card-flush h-md-100">
			<div class="card-body d-flex flex-column justify-content-between">
				<span class="fs-7 fw-semibold text-gray-500 text-uppercase">{{ __('Roles') }}</span>
				<span class="fs-2hx fw-bold text-gray-800">{{ $adminStats['role_count'] }}</span>
				<a href="{{ route('admin.roles.index') }}" class="fs-8 fw-semibold text-primary">{{ __('Manage') }} →</a>
			</div>
		</div>
	</div>
	<div class="col-xl-3 col-md-6">
		<div class="card card-flush h-md-100">
			<div class="card-body d-flex flex-column justify-content-between">
				<span class="fs-7 fw-semibold text-gray-500 text-uppercase">{{ __('Active subscriptions') }}</span>
				<span class="fs-2hx fw-bold text-gray-800">{{ $adminStats['active_subscriptions'] }}</span>
				<a href="{{ route('billing.plans') }}" class="fs-8 fw-semibold text-primary">{{ __('Plans') }} →</a>
			</div>
		</div>
	</div>
	<div class="col-xl-3 col-md-6">
		<div class="card card-flush h-md-100">
			<div class="card-body d-flex flex-column justify-content-between">
				<span class="fs-7 fw-semibold text-gray-500 text-uppercase">{{ __('Landlord accounts') }}</span>
				<span class="fs-2hx fw-bold text-gray-800">{{ $adminStats['landlord_count'] }}</span>
				<span class="fs-8 text-muted">{{ __('Users with landlord role') }}</span>
			</div>
		</div>
	</div>
</div>
<div class="card mb-5">
	<div class="card-header border-0 pt-6">
		<h3 class="card-title fw-bold m-0">{{ __('New user registrations (last 6 months)') }}</h3>
	</div>
	<div class="card-body pt-0">
		<div id="kt_dashboard_admin_chart" style="height: 320px;"></div>
	</div>
</div>
