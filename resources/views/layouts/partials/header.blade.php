@php
	$pathSegments = collect(explode('/', trim(request()->path(), '/')))->filter()->values();
@endphp
					<!--begin::Header-->
					<div id="kt_header" style="" class="header align-items-stretch">
						<!--begin::Brand-->
						<div class="header-brand">
							<!--begin::Logo-->
							<a href="{{ route('dashboard') }}" class="d-flex align-items-center">
								<img src="{{ asset('assets/media/logos/re-estate-mark-dark.svg') }}" alt="{{ config('app.name', 'Residia') }}" class="theme-light-show app-header-brand-logo" width="36" height="36" />
								<img src="{{ asset('assets/media/logos/re-estate-mark-light.svg') }}" alt="{{ config('app.name', 'Residia') }}" class="theme-dark-show rounded-2 app-header-brand-logo" width="36" height="36" style="background: rgba(15,23,42,0.55);" />
							</a>
							<!--end::Logo-->
							<!--begin::Aside minimize-->
							<div id="kt_aside_toggle" class="btn btn-icon w-auto px-0 btn-active-color-primary aside-minimize" data-kt-toggle="true" data-kt-toggle-state="active" data-kt-toggle-target="body" data-kt-toggle-name="aside-minimize">
								<i class="ki-duotone ki-entrance-right fs-1 me-n1 minimize-default">
									<span class="path1"></span>
									<span class="path2"></span>
								</i>
								<i class="ki-duotone ki-entrance-left fs-1 minimize-active">
									<span class="path1"></span>
									<span class="path2"></span>
								</i>
							</div>
							<!--end::Aside minimize-->
							<!--begin::Aside toggle-->
							<div class="d-flex align-items-center d-lg-none me-n2" title="{{ __('Show aside menu') }}">
								<div class="btn btn-icon btn-active-color-primary w-30px h-30px" id="kt_aside_mobile_toggle">
									<i class="ki-duotone ki-abstract-14 fs-1">
										<span class="path1"></span>
										<span class="path2"></span>
									</i>
								</div>
							</div>
							<!--end::Aside toggle-->
						</div>
						<!--end::Brand-->
						<!--begin::Toolbar-->
						<div class="toolbar d-flex align-items-stretch">
							<!--begin::Toolbar container-->
							<div class="container-xxl py-6 py-lg-0 d-flex flex-column flex-lg-row align-items-lg-stretch justify-content-lg-between">
								<!--begin::Page title-->
								<div class="page-title d-flex justify-content-center flex-column me-5">
									<h1 class="d-flex flex-column text-dark fw-bold fs-3 mb-0">@hasSection('title') @yield('title') @else {{ config('app.name') }} @endif</h1>
									<ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 pt-1">
										<li class="breadcrumb-item text-muted">
											<a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">{{ __('Home') }}</a>
										</li>
										@foreach ($pathSegments as $i => $seg)
											<li class="breadcrumb-item">
												<span class="bullet bg-gray-300 w-5px h-2px"></span>
											</li>
											<li class="breadcrumb-item {{ $loop->last ? 'text-dark' : 'text-muted' }}">
												@if ($loop->last)
													{{ \Illuminate\Support\Str::headline(str_replace('-', ' ', $seg)) }}
												@else
													<span class="text-muted">{{ \Illuminate\Support\Str::headline(str_replace('-', ' ', $seg)) }}</span>
												@endif
											</li>
										@endforeach
									</ul>
								</div>
								<!--end::Page title-->
								<!--begin::Action group-->
								<div class="d-flex align-items-stretch overflow-auto pt-3 pt-lg-0 gap-2 gap-lg-3">
									<!--begin::Notifications-->
									<div class="d-flex align-items-center">
										<div class="btn btn-icon btn-color-gray-600 btn-active-color-primary position-relative" data-kt-menu-trigger="click" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
											<i class="ki-duotone ki-notification-status fs-1">
												<span class="path1"></span>
												<span class="path2"></span>
												<span class="path3"></span>
												<span class="path4"></span>
											</i>
											@if (($headerNotificationCount ?? 0) > 0)
												<span class="position-absolute top-0 start-50 translate-middle badge badge-circle badge-danger min-w-18px h-18px fs-10 p-0 d-flex align-items-center justify-content-center">{{ $headerNotificationCount > 9 ? '9+' : $headerNotificationCount }}</span>
											@endif
										</div>
										<div class="menu menu-sub menu-sub-dropdown menu-column w-350px w-lg-375px" data-kt-menu="true">
											<div class="rounded-top px-6 py-5" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 55%, #0c4a6e 100%);">
												<h3 class="text-white fw-semibold mb-0">{{ __('Notifications') }}</h3>
												<span class="text-white text-opacity-75 fs-8">{{ __('Rent, leases, and maintenance that need attention') }}</span>
											</div>
											<div class="scroll-y mh-325px my-2 px-5">
												@forelse ($headerNotificationItems ?? [] as $note)
													@php
														$iconWrap = match ($note['tone'] ?? 'primary') {
															'danger' => ['wrap' => 'bg-light-danger', 'icon' => 'ki-dollar', 'color' => 'text-danger'],
															'warning' => ['wrap' => 'bg-light-warning', 'icon' => 'ki-calendar', 'color' => 'text-warning'],
															default => ['wrap' => 'bg-light-primary', 'icon' => 'ki-wrench', 'color' => 'text-primary'],
														};
													@endphp
													<div class="d-flex flex-stack py-4 @if(! $loop->last) border-bottom border-gray-200 @endif">
														<div class="d-flex align-items-center min-w-0 me-2">
															<div class="symbol symbol-40px me-3 flex-shrink-0">
																<span class="symbol-label {{ $iconWrap['wrap'] }}">
																	<i class="ki-duotone {{ $iconWrap['icon'] }} fs-2 {{ $iconWrap['color'] }}">
																		<span class="path1"></span><span class="path2"></span>
																		@if (($note['tone'] ?? '') === 'danger')<span class="path3"></span>@endif
																	</i>
																</span>
															</div>
															<div class="mb-0 min-w-0">
																<div class="fs-6 text-gray-800 fw-bold text-truncate">{{ $note['title'] }}</div>
																<div class="fs-7 text-muted text-truncate">{{ $note['subtitle'] }}</div>
															</div>
														</div>
														<a href="{{ $note['url'] }}" class="btn btn-sm btn-light-primary flex-shrink-0">{{ __('Open') }}</a>
													</div>
												@empty
													<div class="text-center py-10 px-3">
														<i class="ki-duotone ki-information-2 fs-3x text-gray-400 mb-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
														<p class="text-gray-600 fw-semibold mb-1">{{ __('You are all caught up') }}</p>
														<p class="text-muted fs-7 mb-0">{{ __('Alerts for overdue rent, ending leases, and open maintenance will appear here.') }}</p>
													</div>
												@endforelse
											</div>
											<div class="py-3 px-5 text-center border-top d-flex flex-wrap justify-content-center gap-2">
												<a href="{{ route('dashboard') }}" class="btn btn-sm btn-light">{{ __('Dashboard') }}</a>
												@if (! auth()->user()->hasRole('tenant') || auth()->user()->hasAnyRole(['admin', 'landlord', 'user']))
													<a href="{{ route('rental.payments.index') }}" class="btn btn-sm btn-light-primary">{{ __('Rent payments') }}</a>
												@endif
											</div>
										</div>
									</div>
									<!--end::Notifications-->
									<!--begin::Shortcuts-->
									<div class="d-flex align-items-center">
										<div class="btn btn-icon btn-color-gray-600 btn-active-color-primary" data-kt-menu-trigger="click" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
											<i class="ki-duotone ki-element-11 fs-1">
												<span class="path1"></span>
												<span class="path2"></span>
												<span class="path3"></span>
												<span class="path4"></span>
											</i>
										</div>
										<div class="menu menu-sub menu-sub-dropdown menu-sub-dropdown-end menu-column w-250px w-lg-325px" data-kt-menu="true">
											<div class="d-flex flex-column-fluid flex-center px-6 py-5 rounded-top" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
												<h4 class="text-white fw-bold mb-0">{{ __('Shortcuts') }}</h4>
											</div>
											<div class="row g-0 px-3 py-5">
												<div class="col-6 mb-3">
													<a href="{{ route('rental.properties.index') }}" class="btn btn-active-color-primary border border-gray-300 border-dashed rounded min-w-100px py-3 px-4 text-center bg-hover-light-primary w-100">
														<i class="ki-duotone ki-home-2 fs-2x text-primary mb-2"><span class="path1"></span><span class="path2"></span></i>
														<span class="text-gray-800 fw-semibold fs-7 d-block">{{ __('Properties') }}</span>
													</a>
												</div>
												<div class="col-6 mb-3">
													<a href="{{ route('rental.leases.index') }}" class="btn btn-active-color-primary border border-gray-300 border-dashed rounded min-w-100px py-3 px-4 text-center bg-hover-light-primary w-100">
														<i class="ki-duotone ki-document fs-2x text-success mb-2"><span class="path1"></span><span class="path2"></span></i>
														<span class="text-gray-800 fw-semibold fs-7 d-block">{{ __('Leases') }}</span>
													</a>
												</div>
												<div class="col-6 mb-3">
													<a href="{{ route('rental.payments.index') }}" class="btn btn-active-color-primary border border-gray-300 border-dashed rounded min-w-100px py-3 px-4 text-center bg-hover-light-primary w-100">
														<i class="ki-duotone ki-calendar-tick fs-2x text-warning mb-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
														<span class="text-gray-800 fw-semibold fs-7 d-block">{{ __('Payments') }}</span>
													</a>
												</div>
												<div class="col-6 mb-3">
													<a href="{{ route('rental.transactions.index') }}" class="btn btn-active-color-primary border border-gray-300 border-dashed rounded min-w-100px py-3 px-4 text-center bg-hover-light-primary w-100">
														<i class="ki-duotone ki-notepad fs-2x text-info mb-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
														<span class="text-gray-800 fw-semibold fs-7 d-block">{{ __('Income / expense') }}</span>
													</a>
												</div>
											</div>
										</div>
									</div>
									<!--end::Shortcuts-->
									<div class="bullet bg-secondary h-35px w-1px mx-1 d-none d-sm-block"></div>
									<!--begin::User-->
									<div class="d-flex align-items-center">
										<div class="cursor-pointer symbol symbol-35px symbol-md-40px" data-kt-menu-trigger="{default:'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
											<div class="symbol-label fs-6 fw-semibold bg-light-primary text-primary">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
										</div>
										<div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-275px" data-kt-menu="true">
											<div class="menu-item px-3">
												<div class="menu-content d-flex align-items-center px-3">
													<div class="symbol symbol-50px me-5">
														<div class="symbol-label fs-3 fw-semibold bg-light-primary text-primary">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
													</div>
													<div class="d-flex flex-column">
														<div class="fw-bold d-flex align-items-center fs-5">{{ auth()->user()->name }}</div>
														<span class="fw-semibold text-muted fs-7">{{ auth()->user()->email }}</span>
													</div>
												</div>
											</div>
											<div class="separator my-2"></div>
											<div class="menu-item px-5">
												<a href="{{ route('profile.edit') }}" class="menu-link px-5">{{ __('My profile') }}</a>
											</div>
											<div class="menu-item px-5">
												<a href="{{ route('dashboard') }}" class="menu-link px-5">{{ __('Dashboard') }}</a>
											</div>
											<div class="separator my-2"></div>
											<div class="menu-item px-5">
												<form method="POST" action="{{ route('logout') }}" class="w-100">
													@csrf
													<button type="submit" class="menu-link px-5 btn w-100 text-start border-0 bg-transparent text-gray-800 text-hover-danger">{{ __('Sign out') }}</button>
												</form>
											</div>
										</div>
									</div>
									<!--end::User-->
									<div class="bullet bg-secondary h-35px w-1px mx-1 d-none d-sm-block"></div>
									<!--begin::Theme mode-->
									<div class="d-flex align-items-center">
										<a href="#" class="btn btn-sm btn-icon btn-icon-muted btn-active-icon-primary" data-kt-menu-trigger="{default:'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
											<i class="ki-duotone ki-night-day theme-light-show fs-1">
												<span class="path1"></span>
												<span class="path2"></span>
												<span class="path3"></span>
												<span class="path4"></span>
												<span class="path5"></span>
												<span class="path6"></span>
												<span class="path7"></span>
												<span class="path8"></span>
												<span class="path9"></span>
												<span class="path10"></span>
											</i>
											<i class="ki-duotone ki-moon theme-dark-show fs-1">
												<span class="path1"></span>
												<span class="path2"></span>
											</i>
										</a>
										<div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-title-gray-700 menu-icon-gray-500 menu-active-bg menu-state-color fw-semibold py-4 fs-base w-150px" data-kt-menu="true" data-kt-element="theme-mode-menu">
											<div class="menu-item px-3 my-0">
												<a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="light">
													<span class="menu-icon" data-kt-element="icon">
														<i class="ki-duotone ki-night-day fs-2">
															<span class="path1"></span>
															<span class="path2"></span>
															<span class="path3"></span>
															<span class="path4"></span>
															<span class="path5"></span>
															<span class="path6"></span>
															<span class="path7"></span>
															<span class="path8"></span>
															<span class="path9"></span>
															<span class="path10"></span>
														</i>
													</span>
													<span class="menu-title">{{ __('Light') }}</span>
												</a>
											</div>
											<div class="menu-item px-3 my-0">
												<a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="dark">
													<span class="menu-icon" data-kt-element="icon">
														<i class="ki-duotone ki-moon fs-2">
															<span class="path1"></span>
															<span class="path2"></span>
														</i>
													</span>
													<span class="menu-title">{{ __('Dark') }}</span>
												</a>
											</div>
											<div class="menu-item px-3 my-0">
												<a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="system">
													<span class="menu-icon" data-kt-element="icon">
														<i class="ki-duotone ki-screen fs-2">
															<span class="path1"></span>
															<span class="path2"></span>
															<span class="path3"></span>
															<span class="path4"></span>
														</i>
													</span>
													<span class="menu-title">{{ __('System') }}</span>
												</a>
											</div>
										</div>
									</div>
									<!--end::Theme mode-->
								</div>
								<!--end::Action group-->
							</div>
							<!--end::Toolbar container-->
						</div>
						<!--end::Toolbar-->
					</div>
					<!--end::Header-->
