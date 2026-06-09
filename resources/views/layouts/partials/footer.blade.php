					<!--begin::Footer-->
					<div class="footer py-4 d-flex flex-lg-column" id="kt_footer">
						<div class="container-fluid d-flex flex-column flex-md-row align-items-center justify-content-between">
							<div class="text-dark order-2 order-md-1 text-center text-md-start">
								<span class="text-muted fw-semibold">{{ now()->year }} ©</span>
								<span class="text-gray-800 fw-semibold ms-1">{{ config('app.name', 'Residia') }}</span>
								<span class="text-muted fs-7 ms-2 d-none d-sm-inline">{{ __('Real estate management') }}</span>
							</div>
							<ul class="menu menu-gray-600 menu-hover-primary fw-semibold order-1 mb-3 mb-md-0">
								<li class="menu-item">
									<a href="{{ route('dashboard') }}" class="menu-link px-2">{{ __('Dashboard') }}</a>
								</li>
								<li class="menu-item">
									<a href="{{ route('profile.edit') }}" class="menu-link px-2">{{ __('Profile') }}</a>
								</li>
								@auth
									@if (! auth()->user()->hasRole('tenant'))
										<li class="menu-item">
											<a href="{{ route('billing.plans') }}" class="menu-link px-2">{{ __('Plans & billing') }}</a>
										</li>
									@endif
								@endauth
							</ul>
						</div>
					</div>
					<!--end::Footer-->
