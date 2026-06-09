@extends('layouts.main')
@section('title', __('Record payment'))
@section('content')
	<div class="card">
		<div class="card-header"><h2 class="fw-bold m-0">{{ __('Record payment') }}</h2></div>
		<form method="post" action="{{ route('rental.payments.update', $payment) }}" class="form">
			@csrf @method('PUT')
			<div class="card-body">
				<div class="alert alert-light border mb-6">
					<div class="fw-semibold">{{ __('Lease') }} #{{ $payment->lease_id }} — {{ $payment->lease?->property?->name ?? '—' }}</div>
					<div class="text-muted">{{ __('Tenant') }}: {{ $payment->lease?->tenant?->full_name ?? '—' }}</div>
					<div class="mt-2">{{ __('Due date') }}: <strong>{{ $payment->due_date?->format('Y-m-d') }}</strong> · {{ __('Amount due') }}: <strong>{{ number_format((float) $payment->amount_due, 2) }}</strong></div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label required fw-semibold fs-6" for="amount_paid">{{ __('Amount paid') }}</label>
					<div class="col-lg-4">
						<input id="amount_paid" type="number" step="0.01" min="0" name="amount_paid" value="{{ old('amount_paid', $payment->amount_paid) }}" class="form-control form-control-solid @error('amount_paid') is-invalid @enderror" required />
						@error('amount_paid')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
					<label class="col-lg-2 col-form-label required fw-semibold fs-6" for="status">{{ __('Status') }}</label>
					<div class="col-lg-4">
						<select id="status" name="status" class="form-select form-select-solid @error('status') is-invalid @enderror" required>
							@foreach (['pending' => __('Pending'), 'paid' => __('Paid'), 'overdue' => __('Overdue'), 'waived' => __('Waived')] as $val => $label)
								<option value="{{ $val }}" @selected(old('status', $payment->status) === $val)>{{ $label }}</option>
							@endforeach
						</select>
						@error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="paid_date">{{ __('Paid date') }}</label>
					<div class="col-lg-4">
						<input id="paid_date" type="date" name="paid_date" value="{{ old('paid_date', optional($payment->paid_date)->format('Y-m-d')) }}" class="form-control form-control-solid @error('paid_date') is-invalid @enderror" />
						@error('paid_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="payment_method">{{ __('Payment method') }}</label>
					<div class="col-lg-4">
						<input id="payment_method" type="text" name="payment_method" value="{{ old('payment_method', $payment->payment_method) }}" placeholder="{{ __('Cash, transfer…') }}" class="form-control form-control-solid @error('payment_method') is-invalid @enderror" />
						@error('payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-6">
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="receipt_number">{{ __('Receipt #') }}</label>
					<div class="col-lg-4">
						<input id="receipt_number" type="text" name="receipt_number" value="{{ old('receipt_number', $payment->receipt_number) }}" class="form-control form-control-solid @error('receipt_number') is-invalid @enderror" />
						@error('receipt_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
				<div class="row mb-0">
					<label class="col-lg-2 col-form-label fw-semibold fs-6" for="notes">{{ __('Notes') }}</label>
					<div class="col-lg-10">
						<textarea id="notes" name="notes" rows="2" class="form-control form-control-solid @error('notes') is-invalid @enderror">{{ old('notes', $payment->notes) }}</textarea>
						@error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
					</div>
				</div>
			</div>
			<div class="card-footer d-flex justify-content-end gap-2">
				<a href="{{ route('rental.payments.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
				<button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
			</div>
		</form>
	</div>
@endsection
