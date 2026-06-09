<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>{{ __('New message') }}</title>
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #1e293b; max-width: 560px; margin: 0 auto; padding: 24px;">
	<h1 style="font-size: 1.25rem; margin: 0 0 16px;">{{ __('New message') }}</h1>
	<p style="margin: 0 0 16px;">{{ __('Hello :name,', ['name' => e($recipientName)]) }}</p>
	<p style="margin: 0 0 8px;"><strong>{{ e($subject) }}</strong></p>
	<p style="margin: 0 0 16px; color: #475569; white-space: pre-wrap;">{{ e($bodyPreview) }}</p>
	<p style="margin-top: 24px;"><a href="{{ $conversationUrl }}" style="display: inline-block; padding: 10px 20px; background: #0284c7; color: #fff; text-decoration: none; border-radius: 6px;">{{ __('Open conversation') }}</a></p>
	<p style="margin-top: 24px; color: #64748b; font-size: 0.875rem;">{{ config('app.name') }}</p>
</body>
</html>
