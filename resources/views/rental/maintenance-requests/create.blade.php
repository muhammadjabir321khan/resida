@extends('layouts.main')
@section('title', __('New maintenance request'))
@section('content')
	<div class="card">
		<div class="card-header"><h2 class="fw-bold m-0">{{ __('New maintenance request') }}</h2></div>
		<form method="post" action="{{ route('rental.maintenance-requests.store') }}" class="form">
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
					<div class="col-lg-4">
						<select id="unit_id" name="unit_id" class="form-select form-select-solid @error('unit_id') is-invalid @enderror">
							<option value="">{{ __('— None —') }}</option>
						</select>
						@error('unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="rental_tenant_id">{{ __('Tenant') }}</label>
					<div class="col-lg-4">
						<select id="rental_tenant_id" name="rental_tenant_id" class="form-select form-select-solid @error('rental_tenant_id') is-invalid @enderror">
							<option value="">{{ __('— None —') }}</option>
							@foreach ($tenants as $t)
								<option value="{{ $t->id }}" @selected(old('rental_tenant_id') == $t->id)>{{ $t->full_name }}</option>
							@endforeach
						</select>
						@error('rental_tenant_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="category">{{ __('Category') }}</label>
					<div class="col-lg-4">
						<select id="category" name="category" class="form-select form-select-solid @error('category') is-invalid @enderror">
							<option value="">{{ __('— Select —') }}</option>
							@foreach (['plumbing' => __('Plumbing'), 'electrical' => __('Electrical'), 'hvac' => __('HVAC'), 'appliance' => __('Appliance'), 'structure' => __('Structure'), 'other' => __('Other')] as $val => $label)
								<option value="{{ $val }}" @selected(old('category') === $val)>{{ $label }}</option>
							@endforeach
						</select>
						@error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="technician_name">{{ __('Technician') }}</label>
					<div class="col-lg-4">
						<input id="technician_name" type="text" name="technician_name" value="{{ old('technician_name') }}" class="form-control form-control-solid @error('technician_name') is-invalid @enderror" />
						@error('technician_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label required fw-semibold fs-6" for="title">{{ __('Title') }}</label>
					<div class="col-lg-10">
						<input id="title" type="text" name="title" value="{{ old('title') }}" class="form-control form-control-solid @error('title') is-invalid @enderror" required />
						@error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="description">{{ __('Description') }}</label>
					<div class="col-lg-10">
						<textarea id="description" name="description" rows="4" class="form-control form-control-solid @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
						@error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="estimated_cost">{{ __('Estimated cost') }}</label>
					<div class="col-lg-4">
						<input id="estimated_cost" type="number" step="0.01" min="0" name="estimated_cost" value="{{ old('estimated_cost') }}" class="form-control form-control-solid @error('estimated_cost') is-invalid @enderror" />
						@error('estimated_cost')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="actual_cost">{{ __('Actual cost') }}</label>
					<div class="col-lg-4">
						<input id="actual_cost" type="number" step="0.01" min="0" name="actual_cost" value="{{ old('actual_cost') }}" class="form-control form-control-solid @error('actual_cost') is-invalid @enderror" />
						@error('actual_cost')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label required fw-semibold fs-6" for="status">{{ __('Status') }}</label>
					<div class="col-lg-4">
						<select id="status" name="status" class="form-select form-select-solid @error('status') is-invalid @enderror" required>
							@foreach (['open' => __('Open'), 'in_progress' => __('In progress'), 'completed' => __('Completed'), 'cancelled' => __('Cancelled')] as $val => $label)
								<option value="{{ $val }}" @selected(old('status', 'open') === $val)>{{ $label }}</option>
							@endforeach
						</select>
						@error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
					<label class="col-lg-2 col-form-label required fw-semibold fs-6" for="priority">{{ __('Priority') }}</label>
					<div class="col-lg-4">
						<select id="priority" name="priority" class="form-select form-select-solid @error('priority') is-invalid @enderror" required>
							@foreach (['low' => __('Low'), 'medium' => __('Medium'), 'high' => __('High'), 'urgent' => __('Urgent')] as $val => $label)
								<option value="{{ $val }}" @selected(old('priority', 'medium') === $val)>{{ $label }}</option>
							@endforeach
						</select>
						@error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-0">
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="reported_on">{{ __('Reported on') }}</label>
					<div class="col-lg-4">
						<input id="reported_on" type="date" name="reported_on" value="{{ old('reported_on', now()->format('Y-m-d')) }}" class="form-control form-control-solid @error('reported_on') is-invalid @enderror" />
						@error('reported_on')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="completed_at">{{ __('Completed at') }}</label>
					<div class="col-lg-4">
						<input id="completed_at" type="datetime-local" name="completed_at" value="{{ old('completed_at') }}" class="form-control form-control-solid @error('completed_at') is-invalid @enderror" />
						@error('completed_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
			</div>
			<div class="card-footer d-flex justify-content-end gap-2">
				<a href="{{ route('rental.maintenance-requests.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
				<button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
			</div>
		</form>
	</div>
@endsection
@push('scripts')
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			const units = @json($unitsForJs);
			const emptyLabel = @json(__('— None —'));
			const prop = document.getElementById('property_id');
			const unitSel = document.getElementById('unit_id');
			const oldUnit = @json(old('unit_id'));
			function refreshUnits() {
				const pid = parseInt(prop.value, 10) || 0;
				const cur = unitSel.value;
				unitSel.innerHTML = '<option value="">' + emptyLabel + '</option>';
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
