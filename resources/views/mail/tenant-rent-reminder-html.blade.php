<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>{{ __('Rent reminder') }}</title>
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #1e293b; max-width: 560px; margin: 0 auto; padding: 24px;">
	<h1 style="font-size: 1.25rem; margin: 0 0 16px;">
		@switch(true)
			@case(str_starts_with($reminderType, 'upcoming_'))
				{{ __('Rent due soon') }}
				@break
			@case($reminderType === 'due_today')
				{{ __('Rent is due today') }}
				@break
			@default
				{{ __('Overdue rent notice') }}
		@endswitch
	</h1>
	<p style="margin: 0 0 16px;">{{ __('Hello :name,', ['name' => e($tenantName)]) }}</p>
	<p style="margin: 0 0 16px;">
		@switch(true)
			@case(str_starts_with($reminderType, 'upcoming_'))
				{{ __('This is a friendly reminder that your rent payment is due on :date.', ['date' => $dueDate]) }}
				@break
			@case($reminderType === 'due_today')
				{{ __('Your rent payment is due today (:date).', ['date' => $dueDate]) }}
				@break
			@default
				{{ __('Your rent payment was due on :date and is now overdue. Please contact your landlord if you need assistance.', ['date' => $dueDate]) }}
		@endswitch
	</p>
	<table style="width: 100%; background: #f1f5f9; border-radius: 8px; padding: 16px; margin-bottom: 20px;">
		<tr><td><strong>{{ __('Property') }}</strong></td><td>{{ e($propertyName) }}</td></tr>
		@if ($unitLabel)
			<tr><td><strong>{{ __('Unit') }}</strong></td><td>{{ e($unitLabel) }}</td></tr>
		@endif
		<tr><td><strong>{{ __('Due date') }}</strong></td><td>{{ $dueDate }}</td></tr>
		<tr><td><strong>{{ __('Amount due') }}</strong></td><td>{{ $amountDue }}</td></tr>
	</table>
	<p style="margin-top: 24px;"><a href="{{ $dashboardUrl }}" style="display: inline-block; padding: 10px 20px; background: #0284c7; color: #fff; text-decoration: none; border-radius: 6px;">{{ __('Open tenant portal') }}</a></p>
	@if (! empty($payUrl))
		<p style="margin-top: 12px;"><a href="{{ $payUrl }}" style="display: inline-block; padding: 10px 20px; background: #0f766e; color: #fff; text-decoration: none; border-radius: 6px;">{{ __('Pay rent online') }}</a></p>
		<p style="margin-top: 8px; color: #64748b; font-size: 0.8125rem;">{{ __('Sign in to your tenant portal, then complete payment securely with Stripe.') }}</p>
	@endif
	<p style="margin-top: 24px; color: #64748b; font-size: 0.875rem;">{{ config('app.name') }}</p>
</body>
</html>
