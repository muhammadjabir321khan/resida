@extends('layouts.main')
@section('title', __('Edit tenant'))
@section('content')
	<div class="card">
		<div class="card-header"><h2 class="fw-bold m-0">{{ __('Edit tenant') }}</h2></div>
		@if (filled($tenant->email))
			<div class="card-body border-bottom py-4">
				<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
					<div>
						<div class="fw-semibold text-gray-800">{{ __('Tenant portal') }}</div>
						<div class="text-muted fs-7 mt-1">{{ $tenant->portalStatusLabel() }}</div>
					</div>
					@if (! $tenant->isPortalLinked())
						<form method="post" action="{{ route('rental.tenants.invite', $tenant) }}">
							@csrf
							<button type="submit" class="btn btn-sm btn-light-info">{{ $tenant->hasPendingInvite() ? __('Resend invite') : __('Send portal invite') }}</button>
						</form>
					@endif
				</div>
			</div>
		@endif
		<form method="post" action="{{ route('rental.tenants.update', $tenant) }}" class="form">
			@csrf @method('PUT')
			<div class="card-body">
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label required fw-semibold fs-6" for="full_name">{{ __('Full name') }}</label>
					<div class="col-lg-10">
						<input id="full_name" type="text" name="full_name" value="{{ old('full_name', $tenant->full_name) }}" class="form-control form-control-solid @error('full_name') is-invalid @enderror" required />
						@error('full_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="email">{{ __('Email') }}</label>
					<div class="col-lg-10">
						<input id="email" type="email" name="email" value="{{ old('email', $tenant->email) }}" class="form-control form-control-solid @error('email') is-invalid @enderror" />
						@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="phone">{{ __('Phone') }}</label>
					<div class="col-lg-10">
						<input id="phone" type="text" name="phone" value="{{ old('phone', $tenant->phone) }}" class="form-control form-control-solid @error('phone') is-invalid @enderror" />
						@error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="national_id">{{ __('National ID') }}</label>
					<div class="col-lg-4">
						<input id="national_id" type="text" name="national_id" value="{{ old('national_id', $tenant->national_id) }}" class="form-control form-control-solid @error('national_id') is-invalid @enderror" />
						@error('national_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="nationality">{{ __('Nationality') }}</label>
					<div class="col-lg-4">
						<input id="nationality" type="text" name="nationality" value="{{ old('nationality', $tenant->nationality) }}" class="form-control form-control-solid @error('nationality') is-invalid @enderror" />
						@error('nationality')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="registered_on">{{ __('Registered on') }}</label>
					<div class="col-lg-4">
						<input id="registered_on" type="date" name="registered_on" value="{{ old('registered_on', optional($tenant->registered_on)->format('Y-m-d')) }}" class="form-control form-control-solid @error('registered_on') is-invalid @enderror" />
						@error('registered_on')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label fw-semibold fs-6">{{ __('Emergency contact') }}</label>
					<div class="col-lg-5">
						<input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $tenant->emergency_contact_name) }}" placeholder="{{ __('Name') }}" class="form-control form-control-solid @error('emergency_contact_name') is-invalid @enderror" />
						@error('emergency_contact_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
					<div class="col-lg-5">
						<input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $tenant->emergency_contact_phone) }}" placeholder="{{ __('Phone') }}" class="form-control form-control-solid @error('emergency_contact_phone') is-invalid @enderror" />
						@error('emergency_contact_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-0">
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="notes">{{ __('Notes') }}</label>
					<div class="col-lg-10">
						<textarea id="notes" name="notes" rows="3" class="form-control form-control-solid @error('notes') is-invalid @enderror">{{ old('notes', $tenant->notes) }}</textarea>
						@error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
			</div>
			<div class="card-footer d-flex justify-content-end gap-2">
				<a href="{{ route('rental.tenants.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
				<button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
			</div>
		</form>
	</div>
@endsection
