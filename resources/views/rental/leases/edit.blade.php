@extends('layouts.main')
@section('title', __('Edit lease'))
@section('content')
	<div class="card">
		<div class="card-header"><h2 class="fw-bold m-0">{{ __('Edit lease') }}</h2></div>
		<form method="post" action="{{ route('rental.leases.update', $lease) }}" class="form" id="lease-update-form">
			@csrf @method('PUT')
			<div class="card-body">
				@if ($installmentsCount > 0)
					<div class="alert alert-primary d-flex align-items-center mb-6">
						<span>{{ __(':count scheduled payment(s).', ['count' => $installmentsCount]) }}</span>
						<a href="{{ route('rental.payments.index') }}" class="btn btn-sm btn-light-primary ms-auto">{{ __('Open payments') }}</a>
					</div>
				@endif
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label required fw-semibold fs-6" for="property_id">{{ __('Property') }}</label>
					<div class="col-lg-10">
						<select id="property_id" name="property_id" class="form-select form-select-solid @error('property_id') is-invalid @enderror" required>
							@foreach ($properties as $p)
								<option value="{{ $p->id }}" @selected(old('property_id', $lease->property_id) == $p->id)>{{ $p->name }}</option>
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
						@error('unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label required fw-semibold fs-6" for="tenant_id">{{ __('Tenant') }}</label>
					<div class="col-lg-10">
						<select id="tenant_id" name="tenant_id" class="form-select form-select-solid @error('tenant_id') is-invalid @enderror" required>
							@foreach ($tenants as $t)
								<option value="{{ $t->id }}" @selected(old('tenant_id', $lease->tenant_id) == $t->id)>{{ $t->full_name }}</option>
							@endforeach
						</select>
						@error('tenant_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="start_date">{{ __('Start date') }}</label>
					<div class="col-lg-4">
						<input id="start_date" type="date" name="start_date" value="{{ old('start_date', optional($lease->start_date)->format('Y-m-d')) }}" class="form-control form-control-solid @error('start_date') is-invalid @enderror" />
						@error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="end_date">{{ __('End date') }}</label>
					<div class="col-lg-4">
						<input id="end_date" type="date" name="end_date" value="{{ old('end_date', optional($lease->end_date)->format('Y-m-d')) }}" class="form-control form-control-solid @error('end_date') is-invalid @enderror" />
						@error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label required fw-semibold fs-6" for="monthly_rent">{{ __('Monthly rent') }}</label>
					<div class="col-lg-4">
						<input id="monthly_rent" type="number" step="0.01" min="0" name="monthly_rent" value="{{ old('monthly_rent', $lease->monthly_rent) }}" class="form-control form-control-solid @error('monthly_rent') is-invalid @enderror" required />
						@error('monthly_rent')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
					<label class="col-lg-2 col-form-label required fw-semibold fs-6" for="payment_frequency">{{ __('Payment frequency') }}</label>
					<div class="col-lg-4">
						<select id="payment_frequency" name="payment_frequency" class="form-select form-select-solid @error('payment_frequency') is-invalid @enderror" required>
							@foreach (['monthly' => __('Monthly'), 'quarterly' => __('Quarterly'), 'semi_annual' => __('Semi-annual'), 'annual' => __('Annual')] as $val => $label)
								<option value="{{ $val }}" @selected(old('payment_frequency', $lease->payment_frequency ?? 'monthly') === $val)>{{ $label }}</option>
							@endforeach
						</select>
						@error('payment_frequency')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="security_deposit">{{ __('Security deposit') }}</label>
					<div class="col-lg-4">
						<input id="security_deposit" type="number" step="0.01" min="0" name="security_deposit" value="{{ old('security_deposit', $lease->security_deposit) }}" class="form-control form-control-solid @error('security_deposit') is-invalid @enderror" />
						@error('security_deposit')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
					<label class="col-lg-2 col-form-label required fw-semibold fs-6" for="status">{{ __('Status') }}</label>
					<div class="col-lg-4">
						<select id="status" name="status" class="form-select form-select-solid @error('status') is-invalid @enderror" required>
							@foreach (['draft' => __('Draft'), 'active' => __('Active'), 'expired' => __('Expired'), 'terminated' => __('Terminated')] as $val => $label)
								<option value="{{ $val }}" @selected(old('status', $lease->status) === $val)>{{ $label }}</option>
							@endforeach
						</select>
						@error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label fw-semibold fs-6"></label>
					<div class="col-lg-10">
						<input type="hidden" name="regenerate_payment_schedule" value="0" />
						<label class="form-check form-check-custom form-check-solid">
							<input class="form-check-input" type="checkbox" name="regenerate_payment_schedule" value="1" @checked(old('regenerate_payment_schedule')) />
							<span class="form-check-label fw-semibold text-gray-700">{{ __('Regenerate schedule: delete pending installments and rebuild (works for Draft or Active leases with dates)') }}</span>
						</label>
					</div>
				</div>
				<div class="row mb-0">
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="terms_notes">{{ __('Terms / notes') }}</label>
					<div class="col-lg-10">
						<textarea id="terms_notes" name="terms_notes" rows="3" class="form-control form-control-solid @error('terms_notes') is-invalid @enderror">{{ old('terms_notes', $lease->terms_notes) }}</textarea>
						@error('terms_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
			</div>
		</form>
		<div class="card-footer d-flex justify-content-between gap-2 flex-wrap">
			<form method="post" action="{{ route('rental.leases.message.start', $lease) }}">
				@csrf
				<button type="submit" class="btn btn-light-info">{{ __('Message tenant') }}</button>
			</form>
			<div class="d-flex gap-2">
				<a href="{{ route('rental.leases.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
				<button type="submit" form="lease-update-form" class="btn btn-primary">{{ __('Update') }}</button>
			</div>
		</div>
	</div>

	<div class="card mt-6">
		<div class="card-header"><h3 class="fw-bold m-0">{{ __('Lease documents') }}</h3></div>
		<div class="card-body">
			<form method="post" action="{{ route('rental.leases.documents.store', $lease) }}" enctype="multipart/form-data" class="mb-8">
				@csrf
				<div class="row g-4 align-items-end">
					<div class="col-lg-4">
						<label class="form-label required" for="doc_title">{{ __('Title') }}</label>
						<input id="doc_title" type="text" name="title" value="{{ old('title') }}" class="form-control form-control-solid @error('title') is-invalid @enderror" required />
						@error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
					<div class="col-lg-3">
						<label class="form-label required" for="doc_category">{{ __('Category') }}</label>
						<select id="doc_category" name="category" class="form-select form-select-solid @error('category') is-invalid @enderror" required>
							@foreach ([
								'lease_agreement' => __('Lease agreement'),
								'move_in' => __('Move-in checklist'),
								'identification' => __('Identification'),
								'other' => __('Other'),
							] as $val => $label)
								<option value="{{ $val }}" @selected(old('category') === $val)>{{ $label }}</option>
							@endforeach
						</select>
						@error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
					<div class="col-lg-3">
						<label class="form-label required" for="doc_file">{{ __('File') }}</label>
						<input id="doc_file" type="file" name="file" class="form-control form-control-solid @error('file') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png,.webp" required />
						<div class="form-text">{{ __('PDF or image, max 10 MB') }}</div>
						@error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
					<div class="col-lg-2">
						<input type="hidden" name="is_visible_to_tenant" value="0" />
						<label class="form-check form-check-custom form-check-solid mb-2">
							<input class="form-check-input" type="checkbox" name="is_visible_to_tenant" value="1" @checked(old('is_visible_to_tenant', true)) />
							<span class="form-check-label">{{ __('Tenant can view') }}</span>
						</label>
						<button type="submit" class="btn btn-primary w-100">{{ __('Upload') }}</button>
					</div>
				</div>
			</form>

			<div class="table-responsive">
				<table class="table table-row-bordered align-middle gy-3">
					<thead>
						<tr class="text-muted fw-bold fs-7 text-uppercase">
							<th>{{ __('Title') }}</th>
							<th>{{ __('Category') }}</th>
							<th>{{ __('Tenant access') }}</th>
							<th>{{ __('Uploaded') }}</th>
							<th class="text-end">{{ __('Actions') }}</th>
						</tr>
					</thead>
					<tbody>
						@forelse ($documents as $document)
							<tr>
								<td>{{ $document->title }}</td>
								<td>{{ $document->categoryLabel() }}</td>
								<td>{{ $document->is_visible_to_tenant ? __('Yes') : __('No') }}</td>
								<td>{{ $document->created_at?->format('Y-m-d') }}</td>
								<td class="text-end">
									<a href="{{ route('rental.leases.documents.download', [$lease, $document]) }}" class="btn btn-sm btn-light">{{ __('Download') }}</a>
									<form method="post" action="{{ route('rental.leases.documents.destroy', [$lease, $document]) }}" class="d-inline" onsubmit="return confirm(@json(__('Delete this document?')));">
										@csrf @method('DELETE')
										<button type="submit" class="btn btn-sm btn-light-danger">{{ __('Delete') }}</button>
									</form>
								</td>
							</tr>
						@empty
							<tr><td colspan="5" class="text-muted">{{ __('No documents uploaded yet.') }}</td></tr>
						@endforelse
					</tbody>
				</table>
			</div>
		</div>
	</div>
@endsection
@push('scripts')
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			const units = @json($unitsForJs);
			const emptyUnitLabel = @json(__('— Whole property / not set —'));
			const prop = document.getElementById('property_id');
			const unitSel = document.getElementById('unit_id');
			const selectedUnit = @json(old('unit_id', $lease->unit_id));
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
				if (selectedUnit && units.some(function (u) { return String(u.id) === String(selectedUnit) && u.property_id === pid; })) {
					unitSel.value = String(selectedUnit);
				} else if (cur && units.some(function (u) { return String(u.id) === cur && u.property_id === pid; })) {
					unitSel.value = cur;
				}
			}
			prop.addEventListener('change', refreshUnits);
			refreshUnits();
		});
	</script>
@endpush
