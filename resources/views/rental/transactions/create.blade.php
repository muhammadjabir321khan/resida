@extends('layouts.main')
@section('title', __('Add transaction'))
@section('content')
	<div class="card">
		<div class="card-header"><h2 class="fw-bold m-0">{{ __('Add transaction') }}</h2></div>
		<form method="post" action="{{ route('rental.transactions.store') }}" class="form">
			@csrf
			<div class="card-body">
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label required fw-semibold fs-6" for="direction">{{ __('Direction') }}</label>
					<div class="col-lg-4">
						<select id="direction" name="direction" class="form-select form-select-solid @error('direction') is-invalid @enderror" required>
							<option value="income" @selected(old('direction') === 'income')>{{ __('Income') }}</option>
							<option value="expense" @selected(old('direction') === 'expense')>{{ __('Expense') }}</option>
						</select>
						@error('direction')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
					<label class="col-lg-2 col-form-label required fw-semibold fs-6" for="category">{{ __('Category') }}</label>
					<div class="col-lg-4">
						<select id="category" name="category" class="form-select form-select-solid @error('category') is-invalid @enderror" required>
							@foreach (['rent' => __('Rent'), 'deposit' => __('Deposit'), 'utilities' => __('Utilities'), 'repair' => __('Repair'), 'insurance' => __('Insurance'), 'tax' => __('Tax'), 'other' => __('Other')] as $val => $label)
								<option value="{{ $val }}" @selected(old('category', 'other') === $val)>{{ $label }}</option>
							@endforeach
						</select>
						@error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label required fw-semibold fs-6" for="amount">{{ __('Amount') }}</label>
					<div class="col-lg-4">
						<input id="amount" type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" class="form-control form-control-solid @error('amount') is-invalid @enderror" required />
						@error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
					<label class="col-lg-2 col-form-label required fw-semibold fs-6" for="transaction_date">{{ __('Date') }}</label>
					<div class="col-lg-4">
						<input id="transaction_date" type="date" name="transaction_date" value="{{ old('transaction_date', now()->format('Y-m-d')) }}" class="form-control form-control-solid @error('transaction_date') is-invalid @enderror" required />
						@error('transaction_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="property_id">{{ __('Property') }}</label>
					<div class="col-lg-4">
						<select id="property_id" name="property_id" class="form-select form-select-solid @error('property_id') is-invalid @enderror">
							<option value="">{{ __('— None —') }}</option>
							@foreach ($properties as $p)
								<option value="{{ $p->id }}" @selected(old('property_id') == $p->id)>{{ $p->name }}</option>
							@endforeach
						</select>
						@error('property_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="lease_id">{{ __('Lease') }}</label>
					<div class="col-lg-4">
						<select id="lease_id" name="lease_id" class="form-select form-select-solid @error('lease_id') is-invalid @enderror">
							<option value="">{{ __('— None —') }}</option>
							@foreach ($leases as $l)
								<option value="{{ $l->id }}" @selected(old('lease_id') == $l->id)>#{{ $l->id }} — {{ $l->property?->name ?? '?' }}</option>
							@endforeach
						</select>
						@error('lease_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="unit_id">{{ __('Unit') }}</label>
					<div class="col-lg-4">
						<select id="unit_id" name="unit_id" class="form-select form-select-solid @error('unit_id') is-invalid @enderror">
							<option value="">{{ __('— None —') }}</option>
							@foreach ($units as $u)
								<option value="{{ $u->id }}" @selected(old('unit_id') == $u->id)>{{ $u->property?->name ?? '?' }} — {{ $u->label }}</option>
							@endforeach
						</select>
						@error('unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="vendor_name">{{ __('Vendor') }}</label>
					<div class="col-lg-4">
						<input id="vendor_name" type="text" name="vendor_name" value="{{ old('vendor_name') }}" class="form-control form-control-solid @error('vendor_name') is-invalid @enderror" />
						@error('vendor_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="reference">{{ __('Reference') }}</label>
					<div class="col-lg-4">
						<input id="reference" type="text" name="reference" value="{{ old('reference') }}" class="form-control form-control-solid @error('reference') is-invalid @enderror" />
						@error('reference')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-0">
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="description">{{ __('Description') }}</label>
					<div class="col-lg-10">
						<input id="description" type="text" name="description" value="{{ old('description') }}" class="form-control form-control-solid @error('description') is-invalid @enderror" />
						@error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
			</div>
			<div class="card-footer d-flex justify-content-end gap-2">
				<a href="{{ route('rental.transactions.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
				<button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
			</div>
		</form>
	</div>
@endsection
