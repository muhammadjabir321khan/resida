@isset($stats)
	<div class="row g-5 g-xl-8 mb-5">
		<div class="col-xl-3 col-md-6">
			<div class="card card-flush h-md-100">
				<div class="card-body d-flex flex-column justify-content-between">
					<span class="fs-7 fw-semibold text-gray-500 text-uppercase">{{ __('Properties') }}</span>
					<span class="fs-2hx fw-bold text-gray-800">{{ $stats['properties'] }}</span>
					<a href="{{ route('rental.properties.index') }}" class="fs-8 fw-semibold text-primary">{{ __('Manage') }} →</a>
				</div>
			</div>
		</div>
		<div class="col-xl-3 col-md-6">
			<div class="card card-flush h-md-100">
				<div class="card-body d-flex flex-column justify-content-between">
					<span class="fs-7 fw-semibold text-gray-500 text-uppercase">{{ __('Units') }}</span>
					<span class="fs-2hx fw-bold text-gray-800">{{ $stats['units'] }}</span>
					<a href="{{ route('rental.units.index') }}" class="fs-8 fw-semibold text-primary">{{ __('Manage') }} →</a>
				</div>
			</div>
		</div>
		<div class="col-xl-3 col-md-6">
			<div class="card card-flush h-md-100">
				<div class="card-body d-flex flex-column justify-content-between">
					<span class="fs-7 fw-semibold text-gray-500 text-uppercase">{{ __('Active leases') }}</span>
					<span class="fs-2hx fw-bold text-gray-800">{{ $stats['active_leases'] }}</span>
					<a href="{{ route('rental.leases.index') }}" class="fs-8 fw-semibold text-primary">{{ __('Manage') }} →</a>
				</div>
			</div>
		</div>
		<div class="col-xl-3 col-md-6">
			<div class="card card-flush h-md-100">
				<div class="card-body d-flex flex-column justify-content-between">
					<span class="fs-7 fw-semibold text-gray-500 text-uppercase">{{ __('Open maintenance') }}</span>
					<span class="fs-2hx fw-bold text-gray-800">{{ $stats['open_maintenance'] }}</span>
					<a href="{{ route('rental.maintenance-requests.index') }}" class="fs-8 fw-semibold text-primary">{{ __('Manage') }} →</a>
				</div>
			</div>
		</div>
	</div>
	<div class="row g-5 g-xl-8 mb-8">
		<div class="col-xl-4 col-md-6">
			<div class="card card-bordered h-100">
				<div class="card-body">
					<div class="fs-7 text-muted text-uppercase fw-semibold">{{ __('Monthly rent roll (active)') }}</div>
					<div class="fs-2 fw-bold text-success">{{ number_format($stats['monthly_rent_roll'], 2) }}</div>
				</div>
			</div>
		</div>
		<div class="col-xl-4 col-md-6">
			<div class="card card-bordered h-100">
				<div class="card-body">
					<div class="fs-7 text-muted text-uppercase fw-semibold">{{ __('Income (MTD)') }}</div>
					<div class="fs-2 fw-bold text-primary">{{ number_format($stats['income_mtd'], 2) }}</div>
					<div class="fs-8 text-muted mt-1">{{ __('Expense (MTD)') }}: {{ number_format($stats['expense_mtd'], 2) }}</div>
				</div>
			</div>
		</div>
		<div class="col-xl-4 col-md-6">
			<div class="card card-bordered h-100 border-danger border-dashed">
				<div class="card-body">
					<div class="fs-7 text-muted text-uppercase fw-semibold">{{ __('Overdue rent installments') }}</div>
					<div class="fs-2 fw-bold text-danger">{{ $stats['overdue_installments'] }}</div>
					<a href="{{ route('rental.payments.index') }}" class="fs-8 fw-semibold text-primary">{{ __('Open payments') }} →</a>
				</div>
			</div>
		</div>
	</div>
	<div class="card mb-5">
		<div class="card-header border-0 pt-6">
			<h3 class="card-title fw-bold m-0">{{ __('Income vs expense (last 6 months)') }}</h3>
		</div>
		<div class="card-body pt-0">
			<div id="kt_dashboard_landlord_chart" style="height: 320px;"></div>
		</div>
	</div>
@endisset
