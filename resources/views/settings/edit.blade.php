@extends('layouts.main')

@section('title', __('Settings'))

@section('content')
	<div class="card">
		<div class="card-header border-0 pt-6">
			<h2 class="fw-bold m-0">{{ __('Settings') }}</h2>
			<p class="text-muted fs-7 mb-0 mt-1">{{ __('Currency, language, Stripe, email, and other preferences for your account.') }}</p>
		</div>
		<form method="post" action="{{ route('settings.update') }}" class="form">
			@csrf
			@method('PUT')
			<div class="card-body pt-0">
				@if (session('status'))
					<div class="alert alert-success mb-6">{{ session('status') }}</div>
				@endif
				@if ($errors->any())
					<div class="alert alert-danger mb-6">
						<ul class="mb-0 ps-4">
							@foreach ($errors->all() as $error)
								<li>{{ $error }}</li>
							@endforeach
						</ul>
					</div>
				@endif

				<ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold mb-8" role="tablist">
					<li class="nav-item" role="presentation">
						<button class="nav-link text-active-primary active" type="button" data-bs-toggle="tab" data-bs-target="#tab_general" role="tab">{{ __('General') }}</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link text-active-primary" type="button" data-bs-toggle="tab" data-bs-target="#tab_stripe" role="tab">{{ __('Stripe') }}</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link text-active-primary" type="button" data-bs-toggle="tab" data-bs-target="#tab_email" role="tab">{{ __('Email') }}</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link text-active-primary" type="button" data-bs-toggle="tab" data-bs-target="#tab_other" role="tab">{{ __('Other') }}</button>
					</li>
				</ul>

				<div class="tab-content">
					<div class="tab-pane fade show active" id="tab_general" role="tabpanel">
						<div class="row mb-6">
							<label class="col-lg-3 col-form-label required fw-semibold fs-6" for="default_currency">{{ __('Default currency') }}</label>
							<div class="col-lg-9">
								<input id="default_currency" type="text" name="default_currency" value="{{ old('default_currency', $settings['default_currency']) }}" maxlength="3" class="form-control form-control-solid @error('default_currency') is-invalid @enderror" required autocomplete="off" />
								<div class="form-text">{{ __('ISO 4217 code, e.g. USD, EUR, GBP.') }}</div>
								@error('default_currency')<div class="invalid-feedback">{{ $message }}</div>@enderror
							</div>
						</div>
						<div class="row mb-6">
							<label class="col-lg-3 col-form-label required fw-semibold fs-6" for="locale">{{ __('Language (locale)') }}</label>
							<div class="col-lg-9">
								<input id="locale" type="text" name="locale" value="{{ old('locale', $settings['locale']) }}" class="form-control form-control-solid @error('locale') is-invalid @enderror" required maxlength="32" />
								<div class="form-text">{{ __('Examples: en, en_GB, fr. Must match a Laravel locale folder under lang/ if you add translations.') }}</div>
								@error('locale')<div class="invalid-feedback">{{ $message }}</div>@enderror
							</div>
						</div>
						<div class="row mb-6">
							<label class="col-lg-3 col-form-label required fw-semibold fs-6" for="timezone">{{ __('Timezone') }}</label>
							<div class="col-lg-9">
								<select id="timezone" name="timezone" class="form-select form-select-solid @error('timezone') is-invalid @enderror" required>
									@foreach (\DateTimeZone::listIdentifiers() as $tz)
										<option value="{{ $tz }}" @selected(old('timezone', $settings['timezone']) === $tz)>{{ $tz }}</option>
									@endforeach
								</select>
								@error('timezone')<div class="invalid-feedback">{{ $message }}</div>@enderror
							</div>
						</div>
						<div class="row mb-6">
							<label class="col-lg-3 col-form-label fw-semibold fs-6" for="company_name">{{ __('Company / display name') }}</label>
							<div class="col-lg-9">
								<input id="company_name" type="text" name="company_name" value="{{ old('company_name', $settings['company_name']) }}" class="form-control form-control-solid @error('company_name') is-invalid @enderror" />
								@error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
							</div>
						</div>
						<div class="row mb-6">
							<label class="col-lg-3 col-form-label required fw-semibold fs-6" for="date_format">{{ __('Date format') }}</label>
							<div class="col-lg-9">
								<select id="date_format" name="date_format" class="form-select form-select-solid @error('date_format') is-invalid @enderror" required>
									@foreach (['Y-m-d' => 'Y-m-d (2026-04-26)', 'd/m/Y' => 'd/m/Y (26/04/2026)', 'm/d/Y' => 'm/d/Y (04/26/2026)', 'd.m.Y' => 'd.m.Y (26.04.2026)'] as $val => $label)
										<option value="{{ $val }}" @selected(old('date_format', $settings['date_format']) === $val)>{{ $label }}</option>
									@endforeach
								</select>
								@error('date_format')<div class="invalid-feedback">{{ $message }}</div>@enderror
							</div>
						</div>
					</div>

					<div class="tab-pane fade" id="tab_stripe" role="tabpanel">
						<div class="alert alert-light-primary d-flex align-items-center mb-6">
							<i class="ki-duotone ki-information-5 fs-2hx text-primary me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
							<div class="fs-7">{{ __('Configure Stripe to accept online rent from tenants. Secret keys are encrypted. Leave secret fields empty to keep the current value. If you leave keys empty, the platform Stripe account from .env may be used when RENT_STRIPE_USE_PLATFORM_FALLBACK is enabled.') }}</div>
						</div>
						<div class="row mb-6">
							<label class="col-lg-3 col-form-label required fw-semibold fs-6" for="stripe_mode">{{ __('Mode') }}</label>
							<div class="col-lg-9">
								<select id="stripe_mode" name="stripe_mode" class="form-select form-select-solid @error('stripe_mode') is-invalid @enderror" required>
									<option value="test" @selected(old('stripe_mode', $settings['stripe_mode']) === 'test')>{{ __('Test') }}</option>
									<option value="live" @selected(old('stripe_mode', $settings['stripe_mode']) === 'live')>{{ __('Live') }}</option>
								</select>
								@error('stripe_mode')<div class="invalid-feedback">{{ $message }}</div>@enderror
							</div>
						</div>
						<div class="row mb-6">
							<label class="col-lg-3 col-form-label fw-semibold fs-6" for="stripe_publishable_key">{{ __('Publishable key') }}</label>
							<div class="col-lg-9">
								<input id="stripe_publishable_key" type="text" name="stripe_publishable_key" value="{{ old('stripe_publishable_key', $settings['stripe_publishable_key']) }}" class="form-control form-control-solid @error('stripe_publishable_key') is-invalid @enderror" autocomplete="off" />
								@error('stripe_publishable_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
							</div>
						</div>
						<div class="row mb-6">
							<label class="col-lg-3 col-form-label fw-semibold fs-6" for="stripe_secret_key">{{ __('Secret key') }}</label>
							<div class="col-lg-9">
								<input id="stripe_secret_key" type="password" name="stripe_secret_key" value="" class="form-control form-control-solid @error('stripe_secret_key') is-invalid @enderror" autocomplete="new-password" />
								<div class="form-text">
									@if ($secretsPresent['stripe_secret_key'])
										<span class="text-success">{{ __('A secret key is saved.') }}</span>
									@else
										{{ __('Not set.') }}
									@endif
								</div>
								@error('stripe_secret_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
							</div>
						</div>
						<div class="row mb-6">
							<label class="col-lg-3 col-form-label fw-semibold fs-6" for="stripe_webhook_secret">{{ __('Webhook signing secret') }}</label>
							<div class="col-lg-9">
								<input id="stripe_webhook_secret" type="password" name="stripe_webhook_secret" value="" class="form-control form-control-solid @error('stripe_webhook_secret') is-invalid @enderror" autocomplete="new-password" />
								<div class="form-text">
									@if ($secretsPresent['stripe_webhook_secret'])
										<span class="text-success">{{ __('A webhook secret is saved.') }}</span>
									@else
										{{ __('Not set.') }}
									@endif
								</div>
								@error('stripe_webhook_secret')<div class="invalid-feedback">{{ $message }}</div>@enderror
							</div>
						</div>
					</div>

					<div class="tab-pane fade" id="tab_email" role="tabpanel">
						<div class="alert alert-light d-flex align-items-center mb-6">
							<div class="fs-7">{{ __('These values are stored for your records and future features. Laravel still sends mail using config from .env unless you wire runtime config to these values.') }}</div>
						</div>
						<div class="row mb-6">
							<label class="col-lg-3 col-form-label fw-semibold fs-6" for="mail_from_address">{{ __('From address') }}</label>
							<div class="col-lg-9">
								<input id="mail_from_address" type="email" name="mail_from_address" value="{{ old('mail_from_address', $settings['mail_from_address']) }}" class="form-control form-control-solid @error('mail_from_address') is-invalid @enderror" />
								@error('mail_from_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
							</div>
						</div>
						<div class="row mb-6">
							<label class="col-lg-3 col-form-label fw-semibold fs-6" for="mail_from_name">{{ __('From name') }}</label>
							<div class="col-lg-9">
								<input id="mail_from_name" type="text" name="mail_from_name" value="{{ old('mail_from_name', $settings['mail_from_name']) }}" class="form-control form-control-solid @error('mail_from_name') is-invalid @enderror" />
								@error('mail_from_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
							</div>
						</div>
						<div class="separator my-8"></div>
						<h4 class="fs-5 fw-bold mb-6">{{ __('SMTP (optional)') }}</h4>
						<div class="row mb-6">
							<label class="col-lg-3 col-form-label fw-semibold fs-6" for="smtp_host">{{ __('SMTP host') }}</label>
							<div class="col-lg-9">
								<input id="smtp_host" type="text" name="smtp_host" value="{{ old('smtp_host', $settings['smtp_host']) }}" class="form-control form-control-solid @error('smtp_host') is-invalid @enderror" />
								@error('smtp_host')<div class="invalid-feedback">{{ $message }}</div>@enderror
							</div>
						</div>
						<div class="row mb-6">
							<label class="col-lg-3 col-form-label fw-semibold fs-6" for="smtp_port">{{ __('SMTP port') }}</label>
							<div class="col-lg-4">
								<input id="smtp_port" type="number" name="smtp_port" value="{{ old('smtp_port', $settings['smtp_port']) }}" min="1" max="65535" class="form-control form-control-solid @error('smtp_port') is-invalid @enderror" placeholder="587" />
								@error('smtp_port')<div class="invalid-feedback">{{ $message }}</div>@enderror
							</div>
							<label class="col-lg-2 col-form-label fw-semibold fs-6" for="smtp_encryption">{{ __('Encryption') }}</label>
							<div class="col-lg-3">
								<select id="smtp_encryption" name="smtp_encryption" class="form-select form-select-solid @error('smtp_encryption') is-invalid @enderror">
									<option value="" @selected(old('smtp_encryption', $settings['smtp_encryption']) === '' || old('smtp_encryption', $settings['smtp_encryption']) === null)>{{ __('None') }}</option>
									<option value="tls" @selected(old('smtp_encryption', $settings['smtp_encryption']) === 'tls')>TLS</option>
									<option value="ssl" @selected(old('smtp_encryption', $settings['smtp_encryption']) === 'ssl')>SSL</option>
								</select>
								@error('smtp_encryption')<div class="invalid-feedback">{{ $message }}</div>@enderror
							</div>
						</div>
						<div class="row mb-6">
							<label class="col-lg-3 col-form-label fw-semibold fs-6" for="smtp_username">{{ __('SMTP username') }}</label>
							<div class="col-lg-9">
								<input id="smtp_username" type="text" name="smtp_username" value="{{ old('smtp_username', $settings['smtp_username']) }}" class="form-control form-control-solid @error('smtp_username') is-invalid @enderror" autocomplete="username" />
								@error('smtp_username')<div class="invalid-feedback">{{ $message }}</div>@enderror
							</div>
						</div>
						<div class="row mb-6">
							<label class="col-lg-3 col-form-label fw-semibold fs-6" for="smtp_password">{{ __('SMTP password') }}</label>
							<div class="col-lg-9">
								<input id="smtp_password" type="password" name="smtp_password" value="" class="form-control form-control-solid @error('smtp_password') is-invalid @enderror" autocomplete="new-password" />
								<div class="form-text">
									@if ($secretsPresent['smtp_password'])
										<span class="text-success">{{ __('A password is saved.') }}</span>
									@else
										{{ __('Not set.') }}
									@endif
								</div>
								@error('smtp_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
							</div>
						</div>
					</div>

					<div class="tab-pane fade" id="tab_other" role="tabpanel">
						<div class="row mb-6">
							<label class="col-lg-3 col-form-label fw-semibold fs-6" for="tax_id">{{ __('Tax / registration ID') }}</label>
							<div class="col-lg-9">
								<input id="tax_id" type="text" name="tax_id" value="{{ old('tax_id', $settings['tax_id']) }}" class="form-control form-control-solid @error('tax_id') is-invalid @enderror" />
								@error('tax_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
							</div>
						</div>
						<div class="row mb-6">
							<label class="col-lg-3 col-form-label fw-semibold fs-6" for="business_phone">{{ __('Business phone') }}</label>
							<div class="col-lg-9">
								<input id="business_phone" type="text" name="business_phone" value="{{ old('business_phone', $settings['business_phone']) }}" class="form-control form-control-solid @error('business_phone') is-invalid @enderror" />
								@error('business_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="card-footer d-flex justify-content-end py-6">
				<button type="submit" class="btn btn-primary">{{ __('Save settings') }}</button>
			</div>
		</form>
	</div>
@endsection
