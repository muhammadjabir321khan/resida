@extends('layouts.main')
@section('title', __('Add tenant'))
@section('content')
	<div class="card">
		<div class="card-header"><h2 class="fw-bold m-0">{{ __('Add tenant') }}</h2></div>
		<form method="post" action="{{ route('rental.tenants.store') }}" class="form">
			@csrf
			<div class="card-body">
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label required fw-semibold fs-6" for="full_name">{{ __('Full name') }}</label>
					<div class="col-lg-10">
						<input id="full_name" type="text" name="full_name" value="{{ old('full_name') }}" class="form-control form-control-solid @error('full_name') is-invalid @enderror" required />
						@error('full_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="email">{{ __('Email') }}</label>
					<div class="col-lg-10">
						<input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control form-control-solid @error('email') is-invalid @enderror" />
						@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="phone">{{ __('Phone') }}</label>
					<div class="col-lg-10">
						<input id="phone" type="text" name="phone" value="{{ old('phone') }}" class="form-control form-control-solid @error('phone') is-invalid @enderror" />
						@error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="national_id">{{ __('National ID') }}</label>
					<div class="col-lg-4">
						<input id="national_id" type="text" name="national_id" value="{{ old('national_id') }}" class="form-control form-control-solid @error('national_id') is-invalid @enderror" />
						@error('national_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="nationality">{{ __('Nationality') }}</label>
					<div class="col-lg-4">
						<input id="nationality" type="text" name="nationality" value="{{ old('nationality') }}" class="form-control form-control-solid @error('nationality') is-invalid @enderror" />
						@error('nationality')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="registered_on">{{ __('Registered on') }}</label>
					<div class="col-lg-4">
						<input id="registered_on" type="date" name="registered_on" value="{{ old('registered_on') }}" class="form-control form-control-solid @error('registered_on') is-invalid @enderror" />
						@error('registered_on')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="emergency_contact_name">{{ __('Emergency contact') }}</label>
					<div class="col-lg-5">
						<input id="emergency_contact_name" type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}" placeholder="{{ __('Name') }}" class="form-control form-control-solid @error('emergency_contact_name') is-invalid @enderror" />
						@error('emergency_contact_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
					<div class="col-lg-5">
						<input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}" placeholder="{{ __('Phone') }}" class="form-control form-control-solid @error('emergency_contact_phone') is-invalid @enderror" />
						@error('emergency_contact_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-0">
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="notes">{{ __('Notes') }}</label>
					<div class="col-lg-10">
						<textarea id="notes" name="notes" rows="3" class="form-control form-control-solid @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
						@error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
			</div>
			<div class="card-footer d-flex justify-content-end gap-2">
				<a href="{{ route('rental.tenants.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
				<button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
			</div>
		</form>
	</div>
@endsection
