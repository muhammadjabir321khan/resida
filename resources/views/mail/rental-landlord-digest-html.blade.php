<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>{{ __('Daily rental summary') }}</title>
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #1e293b; max-width: 560px; margin: 0 auto; padding: 24px;">
	<h1 style="font-size: 1.25rem; margin: 0 0 16px;">{{ __('Daily rental summary') }}</h1>
	<p style="margin: 0 0 16px;">{{ __('Hello :name,', ['name' => e($landlordName)]) }}</p>
	<p style="margin: 0 0 16px;">{{ __('Here is what needs attention in your portfolio today.') }}</p>
	<table style="width: 100%; background: #f1f5f9; border-radius: 8px; padding: 16px; margin-bottom: 20px;">
		<tr><td><strong>{{ __('Overdue rent installments') }}</strong></td><td>{{ $overdueCount }}</td></tr>
		<tr><td><strong>{{ __('Leases ending in the next 30 days') }}</strong></td><td>{{ $leasesEndingCount }}</td></tr>
		<tr><td><strong>{{ __('Open maintenance requests') }}</strong></td><td>{{ $openMaintenanceCount }}</td></tr>
	</table>
	@if (count($overdueLines) > 0)
		<h2 style="font-size: 1rem;">{{ __('Overdue (sample)') }}</h2>
		<ul>@foreach ($overdueLines as $line)<li>{{ e($line) }}</li>@endforeach</ul>
	@endif
	@if (count($leaseEndingLines) > 0)
		<h2 style="font-size: 1rem;">{{ __('Ending soon (sample)') }}</h2>
		<ul>@foreach ($leaseEndingLines as $line)<li>{{ e($line) }}</li>@endforeach</ul>
	@endif
	@if (count($maintenanceLines) > 0)
		<h2 style="font-size: 1rem;">{{ __('Maintenance (sample)') }}</h2>
		<ul>@foreach ($maintenanceLines as $line)<li>{{ e($line) }}</li>@endforeach</ul>
	@endif
	<p style="margin-top: 24px;"><a href="{{ $dashboardUrl }}" style="display: inline-block; padding: 10px 20px; background: #0284c7; color: #fff; text-decoration: none; border-radius: 6px;">{{ __('Open dashboard') }}</a></p>
	<p style="margin-top: 24px; color: #64748b; font-size: 0.875rem;">{{ config('app.name') }}</p>
</body>
</html>
