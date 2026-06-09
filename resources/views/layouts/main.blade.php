@php
	$accessRentalUi = str_starts_with(request()->path(), 'rental')
		|| str_starts_with(request()->path(), 'billing')
		|| str_starts_with(request()->path(), 'tenant')
		|| str_starts_with(request()->path(), 'admin')
		|| request()->is('dashboard')
		|| request()->is('settings');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta name="csrf-token" content="{{ csrf_token() }}" />
	<title>@yield('title', config('app.name', 'Residia'))</title>
	<link rel="shortcut icon" href="{{ asset('assets/media/logos/favicon.ico') }}" />
	@if ($accessRentalUi)
		<script>document.documentElement.setAttribute('data-bs-theme', 'light');</script>
	@else
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
	@endif
	<link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
	<link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
	<link href="{{ asset('assets/css/app-table-spacing.css') }}?v=1" rel="stylesheet" type="text/css" />
	@if ($accessRentalUi)
		<link href="{{ asset('assets/css/rental-ms-access-theme.css') }}?v=3" rel="stylesheet" type="text/css" />
	@endif
	<link href="{{ asset('assets/css/app-sidebar-theme.css') }}?v=2" rel="stylesheet" type="text/css" />
	@stack('styles')
	<script>if (window.top != window.self) { window.top.location.replace(window.self.location.href); }</script>
</head>
<body id="kt_body" class="aside-enabled @if($accessRentalUi) rental-ms-access-ui @endif">
	<script>var defaultThemeMode = "light"; var themeMode; if ( document.documentElement ) { if ( document.documentElement.hasAttribute("data-bs-theme-mode")) { themeMode = document.documentElement.getAttribute("data-bs-theme-mode"); } else { if ( localStorage.getItem("data-bs-theme") !== null ) { themeMode = localStorage.getItem("data-bs-theme"); } else { themeMode = defaultThemeMode; } } if (themeMode === "system") { themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light"; } document.documentElement.setAttribute("data-bs-theme", themeMode); }</script>
	<div class="d-flex flex-column flex-root">
		<div class="page d-flex flex-row flex-column-fluid">
			@include('layouts.partials.sidebar')
			<div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
				@include('layouts.partials.header')
				<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
					<div class="post d-flex flex-column-fluid" id="kt_post">
						<div id="kt_content_container" class="container-xxl">
							@if ($accessRentalUi)
								<div class="access-app-caption-bar">
									{{ __('Real Estate Manager') }}
									@if (request()->is('settings'))
										<small class="ms-2">— {{ __('Settings') }}</small>
									@elseif (str_starts_with(request()->path(), 'billing'))
										<small class="ms-2">— {{ __('Plans & billing') }}</small>
									@elseif (str_starts_with(request()->path(), 'rental'))
										<small class="ms-2">— {{ collect(explode('/', trim(request()->path(), '/')))->map(fn ($s) => \Illuminate\Support\Str::headline($s))->join(' › ') }}</small>
									@elseif (str_starts_with(request()->path(), 'tenant'))
										<small class="ms-2">— {{ __('Tenant portal') }}</small>
									@elseif (str_starts_with(request()->path(), 'admin'))
										<small class="ms-2">— {{ __('Administration') }}</small>
									@else
										<small class="ms-2">— {{ __('Dashboard') }}</small>
									@endif
								</div>
							@endif
							@yield('content')
						</div>
					</div>
				</div>
				@include('layouts.partials.footer')
			</div>
		</div>
	</div>
	<div id="kt_scrolltop" class="scrolltop" data-kt-scrolltop="true">
		<i class="ki-duotone ki-arrow-up">
			<span class="path1"></span>
			<span class="path2"></span>
		</i>
	</div>
	<script>var hostUrl = "{{ asset('assets') }}/";</script>
	<script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
	<script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
	<script src="{{ asset('assets/js/widgets.bundle.js') }}"></script>
	<script src="{{ asset('assets/js/custom/widgets.js') }}"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	@stack('scripts')
	@if(session('swal'))
		<script>
			document.addEventListener('DOMContentLoaded', function () {
				Swal.fire(@json(session('swal')));
			});
		</script>
	@endif
</body>
</html>
