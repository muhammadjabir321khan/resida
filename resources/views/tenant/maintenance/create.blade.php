@extends('layouts.main')
@section('title', __('Report maintenance'))
@section('content')
	<div class="card">
		<div class="card-header border-0 pt-6">
			<div class="card-title"><h2 class="fw-bold m-0">{{ __('Report maintenance') }}</h2></div>
			<div class="card-toolbar">
				<a href="{{ route('tenant.maintenance.index') }}" class="btn btn-light">{{ __('Back') }}</a>
			</div>
		</div>
		<div class="card-body">
			@if ($leases->isEmpty())
				<p class="text-muted">{{ __('No leases are linked to your profile, so you cannot submit a request yet.') }}</p>
			@else
				<form method="post" action="{{ route('tenant.maintenance.store') }}" class="form">
					@csrf
					<div class="row mb-6">
						<label class="col-lg-3 col-form-label required fw-semibold fs-6">{{ __('Property') }}</label>
						<div class="col-lg-9">
							<select name="property_id" class="form-select form-select-solid @error('property_id') is-invalid @enderror" required>
								<option value="">{{ __('Select property') }}</option>
								@foreach ($leases->unique('property_id') as $lease)
									<option value="{{ $lease->property_id }}">{{ $lease->property?->name ?? __('Property #').$lease->property_id }}</option>
								@endforeach
							</select>
							@error('property_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
						</div>
					</div>
					<div class="row mb-6">
						<label class="col-lg-3 col-form-label fw-semibold fs-6">{{ __('Unit') }}</label>
						<div class="col-lg-9">
							<select name="unit_id" class="form-select form-select-solid @error('unit_id') is-invalid @enderror">
								<option value="">{{ __('Optional') }}</option>
								@foreach ($leases->whereNotNull('unit_id') as $lease)
									<option value="{{ $lease->unit_id }}">{{ $lease->unit?->label ?? __('Unit #').$lease->unit_id }} — {{ $lease->property?->name }}</option>
								@endforeach
							</select>
							@error('unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
						</div>
					</div>
					<div class="row mb-6">
						<label class="col-lg-3 col-form-label required fw-semibold fs-6">{{ __('Priority') }}</label>
						<div class="col-lg-9">
							<select name="priority" class="form-select form-select-solid" required>
								<option value="low">{{ __('Low') }}</option>
								<option value="medium" selected>{{ __('Medium') }}</option>
								<option value="high">{{ __('High') }}</option>
								<option value="urgent">{{ __('Urgent') }}</option>
							</select>
						</div>
					</div>
					<div class="row mb-6">
						<label class="col-lg-3 col-form-label fw-semibold fs-6">{{ __('Category') }}</label>
						<div class="col-lg-9">
							<input type="text" name="category" class="form-control form-control-solid" maxlength="120" value="{{ old('category') }}" placeholder="{{ __('e.g. Plumbing, HVAC') }}" />
						</div>
					</div>
					<div class="row mb-6">
						<label class="col-lg-3 col-form-label required fw-semibold fs-6">{{ __('Title') }}</label>
						<div class="col-lg-9">
							<input type="text" name="title" class="form-control form-control-solid @error('title') is-invalid @enderror" required maxlength="255" value="{{ old('title') }}" />
							@error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
						</div>
					</div>
					<div class="row mb-6">
						<label class="col-lg-3 col-form-label fw-semibold fs-6">{{ __('Description') }}</label>
						<div class="col-lg-9">
							<textarea name="description" class="form-control form-control-solid" rows="4">{{ old('description') }}</textarea>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-9 offset-lg-3">
							<button type="submit" class="btn btn-primary">{{ __('Submit request') }}</button>
						</div>
					</div>
				</form>
			@endif
		</div>
	</div>
@endsection
