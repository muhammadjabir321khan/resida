@extends('layouts.main')

@section('title', __('Edit role'))

@section('content')
	<div class="card">
		<div class="card-header">
			<h2 class="fw-bold m-0">{{ __('Edit role') }}</h2>
		</div>
		<form class="form" method="post" action="{{ route('admin.roles.update', $role) }}">
			@csrf
			@method('PUT')
			<div class="card-body">
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label required fw-semibold fs-6">{{ __('Name') }}</label>
					<div class="col-lg-10">
						<input type="text" name="name" value="{{ old('name', $role->name) }}" class="form-control form-control-solid @error('name') is-invalid @enderror" required />
						@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-0">
					<label class="col-lg-2 col-form-label fw-semibold fs-6">{{ __('Permissions') }}</label>
					<div class="col-lg-10">
						@php($assigned = old('permissions', $rolePermissionIds))
						<div class="border rounded p-4 bg-light" style="max-height: 360px; overflow-y: auto;">
							<div class="row g-3">
								@foreach ($permissions as $permission)
									<div class="col-md-6 col-lg-4">
										<label class="form-check form-check-custom form-check-solid">
											<input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->id }}" @checked(in_array($permission->id, $assigned, true)) />
											<span class="form-check-label fw-semibold text-gray-700">{{ $permission->name }}</span>
										</label>
									</div>
								@endforeach
							</div>
						</div>
						@error('permissions')<div class="text-danger fs-7 mt-2">{{ $message }}</div>@enderror
						@error('permissions.*')<div class="text-danger fs-7 mt-2">{{ $message }}</div>@enderror
					</div>
				</div>
			</div>
			<div class="card-footer d-flex justify-content-end gap-2">
				<a href="{{ route('admin.roles.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
				<button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
			</div>
		</form>
	</div>
@endsection
