@extends('layouts.main')

@section('title', __('Edit property'))

@section('content')
	<div class="card">
		<div class="card-header"><h2 class="fw-bold m-0">{{ __('Edit property') }}</h2></div>
		<form method="post" action="{{ route('rental.properties.update', $property) }}" class="form" enctype="multipart/form-data">
			@csrf
			@method('PUT')
			<div class="card-body">
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label required fw-semibold fs-6" for="name">{{ __('Name') }}</label>
					<div class="col-lg-10">
						<input id="name" type="text" name="name" value="{{ old('name', $property->name) }}" class="form-control form-control-solid @error('name') is-invalid @enderror" required />
						@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="address_line_1">{{ __('Address line 1') }}</label>
					<div class="col-lg-10">
						<input id="address_line_1" type="text" name="address_line_1" value="{{ old('address_line_1', $property->address_line_1) }}" class="form-control form-control-solid @error('address_line_1') is-invalid @enderror" />
						@error('address_line_1')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="address_line_2">{{ __('Address line 2') }}</label>
					<div class="col-lg-10">
						<input id="address_line_2" type="text" name="address_line_2" value="{{ old('address_line_2', $property->address_line_2) }}" class="form-control form-control-solid @error('address_line_2') is-invalid @enderror" />
						@error('address_line_2')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="city">{{ __('City') }}</label>
					<div class="col-lg-4">
						<input id="city" type="text" name="city" value="{{ old('city', $property->city) }}" class="form-control form-control-solid @error('city') is-invalid @enderror" />
						@error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="state">{{ __('State / Region') }}</label>
					<div class="col-lg-4">
						<input id="state" type="text" name="state" value="{{ old('state', $property->state) }}" class="form-control form-control-solid @error('state') is-invalid @enderror" />
						@error('state')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="postal_code">{{ __('Postal code') }}</label>
					<div class="col-lg-4">
						<input id="postal_code" type="text" name="postal_code" value="{{ old('postal_code', $property->postal_code) }}" class="form-control form-control-solid @error('postal_code') is-invalid @enderror" />
						@error('postal_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="country">{{ __('Country') }}</label>
					<div class="col-lg-4">
						<input id="country" type="text" name="country" value="{{ old('country', $property->country) }}" maxlength="2" class="form-control form-control-solid @error('country') is-invalid @enderror" />
						@error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label required fw-semibold fs-6" for="property_type">{{ __('Property type') }}</label>
					<div class="col-lg-4">
						<select id="property_type" name="property_type" class="form-select form-select-solid @error('property_type') is-invalid @enderror" required>
							@foreach (['residential' => __('Residential'), 'commercial' => __('Commercial'), 'mixed' => __('Mixed use')] as $val => $label)
								<option value="{{ $val }}" @selected(old('property_type', $property->property_type) === $val)>{{ $label }}</option>
							@endforeach
						</select>
						@error('property_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="units_count">{{ __('Unit count') }}</label>
					<div class="col-lg-4">
						<input id="units_count" type="number" name="units_count" value="{{ old('units_count', $property->units_count) }}" min="1" class="form-control form-control-solid @error('units_count') is-invalid @enderror" />
						@error('units_count')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="market_value">{{ __('Market value') }}</label>
					<div class="col-lg-4">
						<input id="market_value" type="number" step="0.01" min="0" name="market_value" value="{{ old('market_value', $property->market_value) }}" class="form-control form-control-solid @error('market_value') is-invalid @enderror" />
						@error('market_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="owner_display_name">{{ __('Owner on title') }}</label>
					<div class="col-lg-4">
						<input id="owner_display_name" type="text" name="owner_display_name" value="{{ old('owner_display_name', $property->owner_display_name) }}" class="form-control form-control-solid @error('owner_display_name') is-invalid @enderror" />
						@error('owner_display_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="photo">{{ __('Photo') }}</label>
					<div class="col-lg-10">
						@if ($property->photo_path)
							<div class="mb-2"><img src="{{ asset('storage/'.$property->photo_path) }}" alt="" class="rounded w-200px" /></div>
						@endif
						<input id="photo" type="file" name="photo" accept="image/*" class="form-control form-control-solid @error('photo') is-invalid @enderror" />
						@error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="notes">{{ __('Notes') }}</label>
					<div class="col-lg-10">
						<textarea id="notes" name="notes" rows="3" class="form-control form-control-solid @error('notes') is-invalid @enderror">{{ old('notes', $property->notes) }}</textarea>
						@error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-0">
					<label class="col-lg-2 col-form-label fw-semibold fs-6">{{ __('Active') }}</label>
					<div class="col-lg-10">
						<label class="form-check form-switch form-check-custom form-check-solid">
							<input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $property->is_active)) />
							<span class="form-check-label fw-semibold text-gray-700">{{ __('Property is active') }}</span>
						</label>
					</div>
				</div>
			</div>
			<div class="card-footer d-flex justify-content-end gap-2">
				<a href="{{ route('rental.properties.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
				<button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
			</div>
		</form>
	</div>
@endsection
