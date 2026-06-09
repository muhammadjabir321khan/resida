@extends('layouts.main')

@section('title', __('Edit permission'))

@section('content')
	<div class="card">
		<div class="card-header">
			<h2 class="fw-bold m-0">{{ __('Edit permission') }}</h2>
		</div>
		<form class="form" method="post" action="{{ route('admin.permissions.update', $permission) }}">
			@csrf
			@method('PUT')
			<div class="card-body">
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label required fw-semibold fs-6">{{ __('Name') }}</label>
					<div class="col-lg-10">
						<input type="text" name="name" value="{{ old('name', $permission->name) }}" class="form-control form-control-solid @error('name') is-invalid @enderror" required />
						@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-0">
					<label class="col-lg-2 col-form-label fw-semibold fs-6">{{ __('Guard name') }}</label>
					<div class="col-lg-10">
						<input type="text" name="guard_name" value="{{ old('guard_name', $permission->guard_name) }}" class="form-control form-control-solid @error('guard_name') is-invalid @enderror" />
						@error('guard_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
			</div>
			<div class="card-footer d-flex justify-content-end gap-2">
				<a href="{{ route('admin.permissions.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
				<button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
			</div>
		</form>
	</div>
@endsection
