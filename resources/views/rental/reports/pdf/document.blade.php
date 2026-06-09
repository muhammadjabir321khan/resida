<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>{{ $report['title'] ?? __('Report') }}</title>
	<style>
		body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
		h1 { font-size: 16px; margin: 0 0 4px 0; }
		.meta { font-size: 9px; color: #555; margin-bottom: 12px; }
		table { width: 100%; border-collapse: collapse; margin-top: 8px; }
		th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; vertical-align: top; }
		th { background: #f0f0f0; font-weight: bold; }
		tfoot td { border: none; font-size: 9px; padding-top: 8px; }
		.currency { text-align: right; }
	</style>
</head>
<body>
	<h1>{{ $report['title'] ?? '' }}</h1>
	@if (!empty($report['subtitle']))
		<div class="meta">{{ $report['subtitle'] }}</div>
	@endif
	<div class="meta">
		@if (!empty($meta['company'])){{ $meta['company'] }} — @endif
		{{ __('Generated') }}: {{ $generatedAt->timezone(config('app.timezone'))->format('Y-m-d H:i') }}
		@if ($generatedBy !== '') — {{ __('By') }}: {{ $generatedBy }}@endif
		@if (!empty($meta['currency'])) — {{ __('Currency') }}: {{ $meta['currency'] }}@endif
	</div>
	<table>
		<thead>
			<tr>
				@foreach ($report['headers'] ?? [] as $h)
					<th>{{ $h }}</th>
				@endforeach
			</tr>
		</thead>
		<tbody>
			@forelse ($report['rows'] ?? [] as $row)
				<tr>
					@foreach ($row as $cell)
						<td>{{ $cell }}</td>
					@endforeach
				</tr>
			@empty
				<tr><td colspan="{{ max(count($report['headers'] ?? []), 1) }}">{{ __('No rows for this filter.') }}</td></tr>
			@endforelse
		</tbody>
		@if (!empty($report['foot']))
			<tfoot>
				<tr><td colspan="{{ max(count($report['headers'] ?? []), 1) }}">
					@foreach ($report['foot'] as $line)
						<div>{{ $line }}</div>
					@endforeach
				</td></tr>
			</tfoot>
		@endif
	</table>
</body>
</html>
