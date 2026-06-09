@extends('layouts.main')

@section('title', __('Edit user'))

@section('content')
	<div class="card">
		<div class="card-header">
			<h2 class="fw-bold m-0">{{ __('Edit user') }}</h2>
		</div>
		<form class="form" method="post" action="{{ route('admin.users.update', $user) }}">
			@csrf
			@method('PUT')
			<div class="card-body">
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label required fw-semibold fs-6">{{ __('Name') }}</label>
					<div class="col-lg-10">
						<input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control form-control-solid @error('name') is-invalid @enderror" required />
						@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label required fw-semibold fs-6">{{ __('Email') }}</label>
					<div class="col-lg-10">
						<input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control form-control-solid @error('email') is-invalid @enderror" required />
						@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label fw-semibold fs-6">{{ __('Password') }}</label>
					<div class="col-lg-10">
						<input type="password" name="password" class="form-control form-control-solid @error('password') is-invalid @enderror" autocomplete="new-password" />
						<div class="form-text">{{ __('Leave blank to keep current password.') }}</div>
						@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label fw-semibold fs-6">{{ __('Confirm password') }}</label>
					<div class="col-lg-10">
						<input type="password" name="password_confirmation" class="form-control form-control-solid" autocomplete="new-password" />
					</div>
				</div>
				<div class="row mb-0">
					<label class="col-lg-2 col-form-label fw-semibold fs-6">{{ __('Roles') }}</label>
					<div class="col-lg-10">
						@php($assigned = old('roles', $user->roles->pluck('name')->all()))
						<div class="row g-3">
							@foreach ($roles as $role)
								<div class="col-md-4">
									<label class="form-check form-check-custom form-check-solid">
										<input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->name }}" @checked(in_array($role->name, $assigned, true)) />
										<span class="form-check-label fw-semibold text-gray-700">{{ $role->name }}</span>
									</label>
								</div>
							@endforeach
						</div>
						@error('roles')<div class="text-danger fs-7 mt-2">{{ $message }}</div>@enderror
						@error('roles.*')<div class="text-danger fs-7 mt-2">{{ $message }}</div>@enderror
					</div>
				</div>
			</div>
			<div class="card-footer d-flex justify-content-end gap-2">
				<a href="{{ route('admin.users.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
				<button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
			</div>
		</form>
	</div>
@endsection
