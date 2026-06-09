@extends('layouts.main')
@section('title', __('Add lease'))
@section('content')
	<div class="card">
		<div class="card-header"><h2 class="fw-bold m-0">{{ __('Add lease') }}</h2></div>
		<form method="post" action="{{ route('rental.leases.store') }}" class="form">
			@csrf
			<div class="card-body">
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label required fw-semibold fs-6" for="property_id">{{ __('Property') }}</label>
					<div class="col-lg-10">
						<select id="property_id" name="property_id" class="form-select form-select-solid @error('property_id') is-invalid @enderror" required>
							<option value="">{{ __('Select property') }}</option>
							@foreach ($properties as $p)
								<option value="{{ $p->id }}" @selected(old('property_id') == $p->id)>{{ $p->name }}</option>
							@endforeach
						</select>
						@error('property_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="unit_id">{{ __('Unit') }}</label>
					<div class="col-lg-10">
						<select id="unit_id" name="unit_id" class="form-select form-select-solid @error('unit_id') is-invalid @enderror">
							<option value="">{{ __('— Whole property / not set —') }}</option>
						</select>
						<div class="form-text">{{ __('Optional. Manage units under Rental → Units.') }}</div>
						@error('unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label required fw-semibold fs-6" for="tenant_id">{{ __('Tenant') }}</label>
					<div class="col-lg-10">
						<select id="tenant_id" name="tenant_id" class="form-select form-select-solid @error('tenant_id') is-invalid @enderror" required>
							<option value="">{{ __('Select tenant') }}</option>
							@foreach ($tenants as $t)
								<option value="{{ $t->id }}" @selected(old('tenant_id') == $t->id)>{{ $t->full_name }}</option>
							@endforeach
						</select>
						@error('tenant_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="start_date">{{ __('Start date') }}</label>
					<div class="col-lg-4">
						<input id="start_date" type="date" name="start_date" value="{{ old('start_date') }}" class="form-control form-control-solid @error('start_date') is-invalid @enderror" />
						@error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="end_date">{{ __('End date') }}</label>
					<div class="col-lg-4">
						<input id="end_date" type="date" name="end_date" value="{{ old('end_date') }}" class="form-control form-control-solid @error('end_date') is-invalid @enderror" />
						@error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label required fw-semibold fs-6" for="monthly_rent">{{ __('Monthly rent') }}</label>
					<div class="col-lg-4">
						<input id="monthly_rent" type="number" step="0.01" min="0" name="monthly_rent" value="{{ old('monthly_rent', '0') }}" class="form-control form-control-solid @error('monthly_rent') is-invalid @enderror" required />
						@error('monthly_rent')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
					<label class="col-lg-2 col-form-label required fw-semibold fs-6" for="payment_frequency">{{ __('Payment frequency') }}</label>
					<div class="col-lg-4">
						<select id="payment_frequency" name="payment_frequency" class="form-select form-select-solid @error('payment_frequency') is-invalid @enderror" required>
							@foreach (['monthly' => __('Monthly'), 'quarterly' => __('Quarterly'), 'semi_annual' => __('Semi-annual'), 'annual' => __('Annual')] as $val => $label)
								<option value="{{ $val }}" @selected(old('payment_frequency', 'monthly') === $val)>{{ $label }}</option>
							@endforeach
						</select>
						@error('payment_frequency')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="security_deposit">{{ __('Security deposit') }}</label>
					<div class="col-lg-4">
						<input id="security_deposit" type="number" step="0.01" min="0" name="security_deposit" value="{{ old('security_deposit') }}" class="form-control form-control-solid @error('security_deposit') is-invalid @enderror" />
						@error('security_deposit')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
					<label class="col-lg-2 col-form-label required fw-semibold fs-6" for="status">{{ __('Status') }}</label>
					<div class="col-lg-4">
						<select id="status" name="status" class="form-select form-select-solid @error('status') is-invalid @enderror" required>
							@foreach (['draft' => __('Draft'), 'active' => __('Active'), 'expired' => __('Expired'), 'terminated' => __('Terminated')] as $val => $label)
								<option value="{{ $val }}" @selected(old('status', 'draft') === $val)>{{ $label }}</option>
							@endforeach
						</select>
						@error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label fw-semibold fs-6"></label>
					<div class="col-lg-10">
						<input type="hidden" name="generate_payment_schedule" value="0" />
						<label class="form-check form-check-custom form-check-solid">
							<input class="form-check-input" type="checkbox" name="generate_payment_schedule" value="1" @checked(old('generate_payment_schedule', true)) />
							<span class="form-check-label fw-semibold text-gray-700">{{ __('Generate rent payment schedule (Draft or Active, with start & end dates)') }}</span>
						</label>
					</div>
				</div>
				<div class="row mb-0">
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="terms_notes">{{ __('Terms / notes') }}</label>
					<div class="col-lg-10">
						<textarea id="terms_notes" name="terms_notes" rows="3" class="form-control form-control-solid @error('terms_notes') is-invalid @enderror">{{ old('terms_notes') }}</textarea>
						@error('terms_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
			</div>
			<div class="card-footer d-flex justify-content-end gap-2">
				<a href="{{ route('rental.leases.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
				<button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
			</div>
		</form>
	</div>
@endsection
@push('scripts')
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			const units = @json($unitsForJs);
			const emptyUnitLabel = @json(__('— Whole property / not set —'));
			const prop = document.getElementById('property_id');
			const unitSel = document.getElementById('unit_id');
			const oldUnit = @json(old('unit_id'));
			function refreshUnits() {
				const pid = parseInt(prop.value, 10) || 0;
				const cur = unitSel.value;
				unitSel.innerHTML = '<option value="">' + emptyUnitLabel + '</option>';
				units.filter(function (u) { return u.property_id === pid; }).forEach(function (u) {
					const o = document.createElement('option');
					o.value = u.id;
					o.textContent = u.label;
					unitSel.appendChild(o);
				});
				if (oldUnit && units.some(function (u) { return String(u.id) === String(oldUnit) && u.property_id === pid; })) {
					unitSel.value = String(oldUnit);
				} else if (cur && units.some(function (u) { return String(u.id) === cur && u.property_id === pid; })) {
					unitSel.value = cur;
				}
			}
			prop.addEventListener('change', refreshUnits);
			refreshUnits();
		});
	</script>
@endpush
