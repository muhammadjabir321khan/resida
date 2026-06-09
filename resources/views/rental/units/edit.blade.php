@extends('layouts.main')
@section('title', __('Edit unit'))
@section('content')
	<div class="card">
		<div class="card-header"><h2 class="fw-bold m-0">{{ __('Edit unit') }}</h2></div>
		<form method="post" action="{{ route('rental.units.update', $unit) }}" class="form">
			@csrf @method('PUT')
			<div class="card-body">
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label required fw-semibold fs-6" for="property_id">{{ __('Property') }}</label>
					<div class="col-lg-10">
						<select id="property_id" name="property_id" class="form-select form-select-solid @error('property_id') is-invalid @enderror" required>
							@foreach ($properties as $p)
								<option value="{{ $p->id }}" @selected(old('property_id', $unit->property_id) == $p->id)>{{ $p->name }}</option>
							@endforeach
						</select>
						@error('property_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label required fw-semibold fs-6" for="label">{{ __('Unit label') }}</label>
					<div class="col-lg-4">
						<input id="label" type="text" name="label" value="{{ old('label', $unit->label) }}" class="form-control form-control-solid @error('label') is-invalid @enderror" required />
						@error('label')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="unit_type">{{ __('Unit type') }}</label>
					<div class="col-lg-4">
						<input id="unit_type" type="text" name="unit_type" value="{{ old('unit_type', $unit->unit_type) }}" class="form-control form-control-solid @error('unit_type') is-invalid @enderror" />
						@error('unit_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="bedrooms">{{ __('Bedrooms') }}</label>
					<div class="col-lg-4">
						<input id="bedrooms" type="number" name="bedrooms" value="{{ old('bedrooms', $unit->bedrooms) }}" min="0" max="50" class="form-control form-control-solid @error('bedrooms') is-invalid @enderror" />
						@error('bedrooms')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="bathrooms">{{ __('Bathrooms') }}</label>
					<div class="col-lg-4">
						<input id="bathrooms" type="number" step="0.1" name="bathrooms" value="{{ old('bathrooms', $unit->bathrooms) }}" min="0" class="form-control form-control-solid @error('bathrooms') is-invalid @enderror" />
						@error('bathrooms')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label required fw-semibold fs-6" for="monthly_rent">{{ __('Default monthly rent') }}</label>
					<div class="col-lg-4">
						<input id="monthly_rent" type="number" step="0.01" min="0" name="monthly_rent" value="{{ old('monthly_rent', $unit->monthly_rent) }}" class="form-control form-control-solid @error('monthly_rent') is-invalid @enderror" required />
						@error('monthly_rent')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label fw-semibold fs-6">{{ __('Amenities') }}</label>
					<div class="col-lg-10">
						<div class="d-flex flex-wrap gap-4">
							@php $selAm = old('amenities', $unit->amenities ?? []); @endphp
							@foreach ($amenityOptions as $am)
								<label class="form-check form-check-custom form-check-solid">
									<input class="form-check-input" type="checkbox" name="amenities[]" value="{{ $am }}" @checked(in_array($am, $selAm, true)) />
									<span class="form-check-label text-capitalize">{{ str_replace('_', ' ', $am) }}</span>
								</label>
							@endforeach
						</div>
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="notes">{{ __('Notes') }}</label>
					<div class="col-lg-10">
						<textarea id="notes" name="notes" rows="2" class="form-control form-control-solid @error('notes') is-invalid @enderror">{{ old('notes', $unit->notes) }}</textarea>
						@error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-0">
					<label class="col-lg-2 col-form-label fw-semibold fs-6">{{ __('Active') }}</label>
					<div class="col-lg-10">
						<label class="form-check form-switch form-check-custom form-check-solid">
							<input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $unit->is_active)) />
							<span class="form-check-label fw-semibold text-gray-700">{{ __('Unit is active') }}</span>
						</label>
					</div>
				</div>
			</div>
			<div class="card-footer d-flex justify-content-end gap-2">
				<a href="{{ route('rental.units.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
				<button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
			</div>
		</form>
	</div>
@endsection
