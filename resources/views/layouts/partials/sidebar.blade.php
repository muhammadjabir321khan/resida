				<!--begin::Aside-->
				<div id="kt_aside" class="aside" data-kt-drawer="true" data-kt-drawer-name="aside" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="{default:'200px', '300px': '250px'}" data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_aside_mobile_toggle">
					<div class="aside-brand flex-column-auto px-5 pt-6 pb-2">
						<a href="{{ route('dashboard') }}" class="aside-brand__link d-flex align-items-center gap-3 text-decoration-none">
							<img src="{{ asset('assets/media/logos/re-estate-mark-light.svg') }}" width="42" height="42" class="aside-brand__mark flex-shrink-0" alt="" />
							<div class="aside-brand__text text-start lh-sm">
								<span class="aside-brand__title d-block text-white fw-bold fs-5">{{ config('app.name', 'Residia') }}</span>
								<span class="aside-brand__tag d-block fs-8 fw-semibold text-uppercase opacity-90">{{ __('Real Estate') }}</span>
							</div>
						</a>
					</div>
					<!--begin::Aside menu-->
					<div class="aside-menu flex-column-fluid">
						<!--begin::Aside Menu-->
						<div class="hover-scroll-overlay-y mx-3 my-5 my-lg-5" id="kt_aside_menu_wrapper" data-kt-scroll="true" data-kt-scroll-height="auto" data-kt-scroll-dependencies="{default: '#kt_header', lg: '#kt_header, #kt_aside'}" data-kt-scroll-wrappers="#kt_aside_menu" data-kt-scroll-offset="5px">
							<!--begin::Menu-->
							<div class="menu menu-column menu-title-gray-800 menu-state-title-primary menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-500" id="kt_aside_menu" data-kt-menu="true">
								@include('layouts.partials.sidebar-admin')
							</div>
							<!--end::Menu-->
						</div>
						<!--end::Aside Menu-->
					</div>
					<!--end::Aside menu-->
				</div>
				<!--end::Aside-->
