<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>{{ __('Tenant portal invite') }}</title>
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #1e293b; max-width: 560px; margin: 0 auto; padding: 24px;">
	<h1 style="font-size: 1.25rem; margin: 0 0 16px;">{{ __('You are invited to the tenant portal') }}</h1>
	<p style="margin: 0 0 16px;">{{ __('Hello :name,', ['name' => e($tenantName)]) }}</p>
	<p style="margin: 0 0 16px;">{{ __(':landlord invited you to access your lease, rent schedule, and maintenance requests online.', ['landlord' => e($landlordName)]) }}</p>
	<p style="margin: 0 0 24px;"><a href="{{ $inviteUrl }}" style="display: inline-block; padding: 10px 20px; background: #0284c7; color: #fff; text-decoration: none; border-radius: 6px;">{{ __('Accept invite') }}</a></p>
	<p style="margin: 0 0 8px; color: #64748b; font-size: 0.875rem;">{{ __('If the button does not work, copy this link into your browser:') }}</p>
	<p style="word-break: break-all; font-size: 0.875rem;">{{ $inviteUrl }}</p>
	<p style="margin-top: 24px; color: #64748b; font-size: 0.875rem;">{{ config('app.name') }}</p>
</body>
</html>
