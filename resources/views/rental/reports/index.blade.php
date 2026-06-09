@extends('layouts.main')
@section('title', __('Reports'))
@section('content')
	@php
		$reportService = app(\App\Services\LandlordReportService::class);
		$exportBase = array_filter([
			'type' => $type,
			'date_from' => $dateFrom->toDateString(),
			'date_to' => $dateTo->toDateString(),
			'property_id' => $propertyId,
		], fn ($v) => $v !== null && $v !== '');
	@endphp
	<div class="card mb-5">
		<div class="card-header border-0 pt-6">
			<div class="card-title">
				<h2 class="fw-bold m-0">{{ __('Reports') }}</h2>
			</div>
			<div class="card-toolbar">
				<span class="text-muted fs-7">{{ __('Preview data, then download PDF or CSV for your records.') }}</span>
			</div>
		</div>
		<div class="card-body pt-0">
			<p class="text-gray-700 mb-6">{{ __('Export property, tenant, lease, rent collection, and income summaries similar to a small real-estate manager.') }}</p>
			<form method="get" action="{{ route('rental.reports.index') }}" class="row g-4 align-items-end">
				<div class="col-md-3">
					<label class="form-label fw-semibold">{{ __('Report') }}</label>
					<select name="type" class="form-select form-select-solid">
						<option value="{{ \App\Services\LandlordReportService::TYPE_RENT_ROLL }}" @selected($type === \App\Services\LandlordReportService::TYPE_RENT_ROLL)>{{ __('Rent roll') }}</option>
						<option value="{{ \App\Services\LandlordReportService::TYPE_RENT_COLLECTIONS }}" @selected($type === \App\Services\LandlordReportService::TYPE_RENT_COLLECTIONS)>{{ __('Rent collections') }}</option>
						<option value="{{ \App\Services\LandlordReportService::TYPE_TENANT_DIRECTORY }}" @selected($type === \App\Services\LandlordReportService::TYPE_TENANT_DIRECTORY)>{{ __('Tenant directory') }}</option>
						<option value="{{ \App\Services\LandlordReportService::TYPE_INCOME_EXPENSE }}" @selected($type === \App\Services\LandlordReportService::TYPE_INCOME_EXPENSE)>{{ __('Income & expenses') }}</option>
						<option value="{{ \App\Services\LandlordReportService::TYPE_PROPERTY_PORTFOLIO }}" @selected($type === \App\Services\LandlordReportService::TYPE_PROPERTY_PORTFOLIO)>{{ __('Property portfolio') }}</option>
					</select>
				</div>
				<div class="col-md-2">
					<label class="form-label fw-semibold">{{ __('From') }}</label>
					<input type="date" name="date_from" class="form-control form-control-solid" value="{{ $dateFrom->toDateString() }}">
				</div>
				<div class="col-md-2">
					<label class="form-label fw-semibold">{{ __('To') }}</label>
					<input type="date" name="date_to" class="form-control form-control-solid" value="{{ $dateTo->toDateString() }}">
				</div>
				<div class="col-md-3">
					<label class="form-label fw-semibold">{{ __('Property') }} <span class="text-muted fw-normal">({{ __('optional') }})</span></label>
					<select name="property_id" class="form-select form-select-solid">
						<option value="">{{ __('All properties') }}</option>
						@foreach ($properties as $p)
							<option value="{{ $p->id }}" @selected((int) $propertyId === (int) $p->id)>{{ $p->name }}</option>
						@endforeach
					</select>
				</div>
				<div class="col-md-2 d-flex gap-2 flex-wrap">
					<button type="submit" class="btn btn-primary">{{ __('Update preview') }}</button>
				</div>
			</form>
			@if (! $reportService->usesDateRange($type))
				<p class="text-muted fs-7 mt-3 mb-0">{{ __('Date range applies to rent collections and income & expenses only.') }}</p>
			@endif
		</div>
	</div>

	<div class="card mb-5">
		<div class="card-header border-0 pt-6">
			<div class="card-title"><h3 class="fw-bold m-0 fs-4">{{ __('Preview') }} — {{ $preview['title'] ?? '' }}</h3></div>
			<div class="card-toolbar d-flex gap-2">
				<a class="btn btn-sm btn-light-primary" href="{{ route('rental.reports.export', array_merge($exportBase, ['format' => 'pdf'])) }}">{{ __('Download PDF') }}</a>
				<a class="btn btn-sm btn-light-success" href="{{ route('rental.reports.export', array_merge($exportBase, ['format' => 'csv'])) }}">{{ __('Download CSV') }}</a>
			</div>
		</div>
		<div class="card-body py-4">
			@if (!empty($preview['subtitle']))
				<p class="text-muted fs-7 mb-4">{{ $preview['subtitle'] }}</p>
			@endif
			<div class="table-responsive">
				<table class="table table-row-bordered align-middle gy-4">
					<thead>
						<tr class="fw-bold text-muted text-uppercase fs-7">
							@foreach ($preview['headers'] ?? [] as $h)
								<th>{{ $h }}</th>
							@endforeach
						</tr>
					</thead>
					<tbody class="text-gray-700 fw-semibold">
						@forelse ($preview['rows'] ?? [] as $row)
							<tr>
								@foreach ($row as $cell)
									<td>{{ $cell }}</td>
								@endforeach
							</tr>
						@empty
							<tr><td colspan="{{ max(count($preview['headers'] ?? []), 1) }}" class="text-muted">{{ __('No data for this filter.') }}</td></tr>
						@endforelse
					</tbody>
					@if (!empty($preview['foot']))
						<tfoot>
							<tr>
								<td colspan="{{ max(count($preview['headers'] ?? []), 1) }}" class="text-muted fs-7 pt-4">
									@foreach ($preview['foot'] as $line)
										<div>{{ $line }}</div>
									@endforeach
								</td>
							</tr>
						</tfoot>
					@endif
				</table>
			</div>
		</div>
	</div>
@endsection
