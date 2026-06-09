@extends('layouts.main')

@section('title', __('Create permission'))

@section('content')
	<div class="card">
		<div class="card-header">
			<h2 class="fw-bold m-0">{{ __('Create permission') }}</h2>
		</div>
		<form class="form" method="post" action="{{ route('admin.permissions.store') }}">
			@csrf
			<div class="card-body">
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label required fw-semibold fs-6">{{ __('Name') }}</label>
					<div class="col-lg-10">
						<input type="text" name="name" value="{{ old('name') }}" class="form-control form-control-solid @error('name') is-invalid @enderror" placeholder="e.g. edit articles" required />
						@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-0">
					<label class="col-lg-2 col-form-label fw-semibold fs-6">{{ __('Guard name') }}</label>
					<div class="col-lg-10">
						<input type="text" name="guard_name" value="{{ old('guard_name', 'web') }}" class="form-control form-control-solid @error('guard_name') is-invalid @enderror" />
						@error('guard_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
			</div>
			<div class="card-footer d-flex justify-content-end gap-2">
				<a href="{{ route('admin.permissions.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
				<button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
			</div>
		</form>
	</div>
@endsection
