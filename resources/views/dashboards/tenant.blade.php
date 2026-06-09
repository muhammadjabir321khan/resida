@php($ctx = $tenantContext ?? ['profile' => null, 'leases' => collect(), 'upcoming' => collect(), 'maintenance_open' => 0, 'paid_history' => collect(), 'recent_maintenance' => collect()])

@if ($ctx['profile'] === null)
	<div class="card">
		<div class="card-body">
			<h3 class="fw-bold mb-3">{{ __('Tenant portal') }}</h3>
			<p class="text-gray-700 mb-0">{{ __('No renter profile is linked to your account yet. Ask your landlord to send you a portal invite, or sign in with the same email they saved on your tenant record.') }}</p>
		</div>
	</div>
@else
	<div class="row g-5 g-xl-8 mb-5">
		<div class="col-md-4">
			<div class="card card-flush h-100">
				<div class="card-body">
					<div class="fs-7 text-muted text-uppercase fw-semibold">{{ __('Your unit') }}</div>
					<div class="fs-4 fw-bold text-gray-900 mt-2">{{ $ctx['profile']->full_name }}</div>
					<p class="fs-7 text-muted mb-0 mt-2">{{ $ctx['profile']->email }}</p>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card card-flush h-100">
				<div class="card-body">
					<div class="fs-7 text-muted text-uppercase fw-semibold">{{ __('Active leases') }}</div>
					<div class="fs-2hx fw-bold text-primary">{{ $ctx['leases']->where('status', 'active')->count() }}</div>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card card-flush h-100">
				<div class="card-body">
					<div class="fs-7 text-muted text-uppercase fw-semibold">{{ __('Open maintenance') }}</div>
					<div class="fs-2hx fw-bold text-warning">{{ $ctx['maintenance_open'] }}</div>
				</div>
			</div>
		</div>
	</div>

	<div class="row g-5 g-xl-8 mb-5">
		<div class="col-md-4">
			<div class="card card-flush h-100">
				<div class="card-body d-flex flex-column">
					<div class="fs-7 text-muted text-uppercase fw-semibold">{{ __('Quick links') }}</div>
					<div class="d-flex flex-column gap-2 mt-3">
						<a href="{{ route('tenant.messages.index') }}" class="btn btn-sm btn-light-primary">{{ __('Messages') }}</a>
						<a href="{{ route('tenant.documents.index') }}" class="btn btn-sm btn-light">{{ __('Lease documents') }}</a>
						<a href="{{ route('tenant.maintenance.create') }}" class="btn btn-sm btn-light-warning">{{ __('Report maintenance') }}</a>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-8">
			<div class="card h-100">
				<div class="card-header border-0 pt-6">
					<h3 class="card-title fw-bold m-0">{{ __('Upcoming rent') }}</h3>
				</div>
				<div class="card-body pt-0">
					<div class="table-responsive">
						<table class="table table-row-bordered align-middle gy-4">
							<thead>
								<tr class="fw-bold text-muted text-uppercase fs-7">
									<th>{{ __('Due') }}</th>
									<th>{{ __('Amount') }}</th>
									<th>{{ __('Status') }}</th>
									<th>{{ __('Property') }}</th>
									<th class="text-end">{{ __('Action') }}</th>
								</tr>
							</thead>
							<tbody>
								@php($stripeService = app(\App\Services\LandlordStripeService::class))
								@forelse ($ctx['upcoming'] as $row)
									<tr>
										<td>{{ $row->due_date?->format('Y-m-d') }}</td>
										<td>{{ number_format((float) $row->amount_due, 2) }}</td>
										<td>
											@php($displayStatus = $row->displayStatus())
											<span class="badge badge-light-{{ $displayStatus === 'overdue' ? 'danger' : 'warning' }}">{{ $displayStatus }}</span>
										</td>
										<td class="text-gray-700">{{ $row->lease?->property?->name ?? '—' }}</td>
										<td class="text-end">
											@if ($row->isPayableOnline() && $stripeService->isConfigured((int) $row->user_id))
												<form method="post" action="{{ route('tenant.rent.checkout', $row->id) }}" class="d-inline">
													@csrf
													<button type="submit" class="btn btn-sm btn-primary">{{ __('Pay now') }}</button>
												</form>
											@else
												<span class="text-muted fs-8">—</span>
											@endif
										</td>
									</tr>
								@empty
									<tr><td colspan="5" class="text-muted">{{ __('No upcoming installments.') }}</td></tr>
								@endforelse
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="row g-5 g-xl-8 mb-5">
		<div class="col-lg-5">
			<div class="card h-100">
				<div class="card-header border-0 pt-6">
					<h3 class="card-title fw-bold m-0">{{ __('Payment mix (all installments)') }}</h3>
				</div>
				<div class="card-body pt-0 d-flex flex-column align-items-center justify-content-center" style="min-height: 280px;">
					<div id="kt_dashboard_tenant_chart" style="width: 100%; max-width: 360px; height: 280px;"></div>
				</div>
			</div>
		</div>
	</div>

	<div class="row g-5 g-xl-8 mb-5">
		<div class="col-lg-6">
			<div class="card h-100">
				<div class="card-header border-0 pt-6 d-flex flex-wrap align-items-center justify-content-between gap-2">
					<h3 class="card-title fw-bold m-0">{{ __('Recent payments') }}</h3>
				</div>
				<div class="card-body pt-0">
					<div class="table-responsive">
						<table class="table table-row-bordered align-middle gy-3 fs-7">
							<thead>
								<tr class="text-muted fw-bold text-uppercase">
									<th>{{ __('Paid') }}</th>
									<th>{{ __('Amount') }}</th>
									<th>{{ __('Property') }}</th>
								</tr>
							</thead>
							<tbody>
								@forelse ($ctx['paid_history'] ?? [] as $row)
									<tr>
										<td>{{ $row->paid_date?->format('Y-m-d') ?? $row->due_date?->format('Y-m-d') ?? '—' }}</td>
										<td>{{ number_format((float) $row->amount_paid, 2) }}</td>
										<td>{{ $row->lease?->property?->name ?? '—' }}</td>
									</tr>
								@empty
									<tr><td colspan="3" class="text-muted">{{ __('No paid installments yet.') }}</td></tr>
								@endforelse
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		<div class="col-lg-6">
			<div class="card h-100">
				<div class="card-header border-0 pt-6 d-flex flex-wrap align-items-center justify-content-between gap-2">
					<h3 class="card-title fw-bold m-0">{{ __('Your maintenance') }}</h3>
					<a href="{{ route('tenant.maintenance.create') }}" class="btn btn-sm btn-primary">{{ __('Report issue') }}</a>
				</div>
				<div class="card-body pt-0">
					<div class="table-responsive">
						<table class="table table-row-bordered align-middle gy-3 fs-7">
							<thead>
								<tr class="text-muted fw-bold text-uppercase">
									<th>{{ __('Title') }}</th>
									<th>{{ __('Status') }}</th>
									<th>{{ __('Property') }}</th>
								</tr>
							</thead>
							<tbody>
								@forelse ($ctx['recent_maintenance'] ?? [] as $row)
									<tr>
										<td>{{ Str::limit($row->title, 40) }}</td>
										<td><span class="badge badge-light">{{ $row->status }}</span></td>
										<td>{{ $row->property?->name ?? '—' }}</td>
									</tr>
								@empty
									<tr><td colspan="3" class="text-muted">{{ __('No requests yet.') }} <a href="{{ route('tenant.maintenance.create') }}">{{ __('Create one') }}</a></td></tr>
								@endforelse
							</tbody>
						</table>
					</div>
					<div class="text-end mt-2">
						<a href="{{ route('tenant.maintenance.index') }}" class="fs-7 fw-semibold">{{ __('View all') }} →</a>
					</div>
				</div>
			</div>
		</div>
	</div>

	@if ($ctx['leases']->isNotEmpty())
		<div class="card">
			<div class="card-header border-0 pt-6">
				<h3 class="card-title fw-bold m-0">{{ __('Your leases') }}</h3>
			</div>
			<div class="card-body pt-0">
				<div class="table-responsive">
					<table class="table table-row-bordered align-middle">
						<thead>
							<tr class="fw-bold text-muted text-uppercase fs-7">
								<th>{{ __('Property') }}</th>
								<th>{{ __('Start') }}</th>
								<th>{{ __('End') }}</th>
								<th>{{ __('Rent') }}</th>
								<th>{{ __('Status') }}</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($ctx['leases'] as $lease)
								<tr>
									<td>{{ $lease->property?->name ?? '—' }}</td>
									<td>{{ $lease->start_date?->format('Y-m-d') }}</td>
									<td>{{ $lease->end_date?->format('Y-m-d') }}</td>
									<td>{{ number_format((float) $lease->monthly_rent, 2) }}</td>
									<td><span class="badge badge-light">{{ $lease->status }}</span></td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>
	@endif
@endif
