<div class="menu-item">
	<a class="menu-link" href="{{ route('dashboard') }}">
		<span class="menu-icon"><i class="ki-duotone ki-element-11 fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i></span>
		<span class="menu-title">{{ __('Dashboard') }}</span>
	</a>
</div>
@if (auth()->user()->hasRole('tenant'))
	<div class="menu-item">
		<a class="menu-link" href="{{ route('tenant.messages.index') }}">
			<span class="menu-icon"><i class="ki-duotone ki-message-text-2 fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i></span>
			<span class="menu-title">{{ __('Messages') }}</span>
		</a>
	</div>
	<div class="menu-item">
		<a class="menu-link" href="{{ route('tenant.documents.index') }}">
			<span class="menu-icon"><i class="ki-duotone ki-folder fs-2"><span class="path1"></span><span class="path2"></span></i></span>
			<span class="menu-title">{{ __('Documents') }}</span>
		</a>
	</div>
	<div class="menu-item">
		<a class="menu-link" href="{{ route('tenant.maintenance.index') }}">
			<span class="menu-icon"><i class="ki-duotone ki-wrench fs-2"><span class="path1"></span><span class="path2"></span></i></span>
			<span class="menu-title">{{ __('My maintenance') }}</span>
		</a>
	</div>
@endif
@if (! auth()->user()->hasRole('tenant'))
	@if (auth()->user()->hasAnyRole(['admin', 'landlord']))
		<div class="menu-item">
			<a class="menu-link" href="{{ route('settings.edit') }}">
				<span class="menu-icon"><i class="ki-duotone ki-setting-2 fs-2"><span class="path1"></span><span class="path2"></span></i></span>
				<span class="menu-title">{{ __('Settings') }}</span>
			</a>
		</div>
	@endif
	<div class="menu-item">
		<a class="menu-link" href="{{ route('billing.plans') }}">
			<span class="menu-icon"><i class="ki-duotone ki-dollar fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i></span>
			<span class="menu-title">{{ __('Plans & billing') }}</span>
		</a>
	</div>
@endif
@if (auth()->user()->hasRole('admin'))
	<div class="menu-item">
		<a class="menu-link" href="{{ route('admin.users.index') }}">
			<span class="menu-icon"><i class="ki-duotone ki-people fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i></span>
			<span class="menu-title">{{ __('Users') }}</span>
		</a>
	</div>
	<div class="menu-item">
		<a class="menu-link" href="{{ route('admin.roles.index') }}">
			<span class="menu-icon"><i class="ki-duotone ki-shield-tick fs-2"><span class="path1"></span><span class="path2"></span></i></span>
			<span class="menu-title">{{ __('Roles') }}</span>
		</a>
	</div>
	<div class="menu-item">
		<a class="menu-link" href="{{ route('admin.permissions.index') }}">
			<span class="menu-icon"><i class="ki-duotone ki-lock-2 fs-2"><span class="path1"></span><span class="path2"></span></i></span>
			<span class="menu-title">{{ __('Permissions') }}</span>
		</a>
	</div>
	<div class="menu-item">
		<a class="menu-link" href="{{ route('admin.activity-log.index') }}">
			<span class="menu-icon"><i class="ki-duotone ki-notepad fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i></span>
			<span class="menu-title">{{ __('Activity log') }}</span>
		</a>
	</div>
@endif
@if (! auth()->user()->hasRole('tenant'))
	<div class="separator my-2"></div>
	<div class="menu-item">
		<a class="menu-link" href="{{ route('rental.properties.index') }}">
			<span class="menu-icon"><i class="ki-duotone ki-home-2 fs-2"><span class="path1"></span><span class="path2"></span></i></span>
			<span class="menu-title">{{ __('Properties') }}</span>
		</a>
	</div>
	<div class="menu-item">
		<a class="menu-link" href="{{ route('rental.units.index') }}">
			<span class="menu-icon"><i class="ki-duotone ki-row-horizontal fs-2"><span class="path1"></span><span class="path2"></span></i></span>
			<span class="menu-title">{{ __('Units') }}</span>
		</a>
	</div>
	<div class="menu-item">
		<a class="menu-link" href="{{ route('rental.tenants.index') }}">
			<span class="menu-icon"><i class="ki-duotone ki-profile-user fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i></span>
			<span class="menu-title">{{ __('Tenants') }}</span>
		</a>
	</div>
	<div class="menu-item">
		<a class="menu-link" href="{{ route('rental.leases.index') }}">
			<span class="menu-icon"><i class="ki-duotone ki-document fs-2"><span class="path1"></span><span class="path2"></span></i></span>
			<span class="menu-title">{{ __('Leases') }}</span>
		</a>
	</div>
	<div class="menu-item">
		<a class="menu-link" href="{{ route('rental.payments.index') }}">
			<span class="menu-icon"><i class="ki-duotone ki-calendar-tick fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i></span>
			<span class="menu-title">{{ __('Rent payments') }}</span>
		</a>
	</div>
	<div class="menu-item">
		<a class="menu-link" href="{{ route('rental.messages.index') }}">
			<span class="menu-icon"><i class="ki-duotone ki-message-text-2 fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i></span>
			<span class="menu-title">{{ __('Messages') }}</span>
		</a>
	</div>
	<div class="menu-item">
		<a class="menu-link" href="{{ route('rental.maintenance-requests.index') }}">
			<span class="menu-icon"><i class="ki-duotone ki-wrench fs-2"><span class="path1"></span><span class="path2"></span></i></span>
			<span class="menu-title">{{ __('Maintenance') }}</span>
		</a>
	</div>
	<div class="menu-item">
		<a class="menu-link" href="{{ route('rental.transactions.index') }}">
			<span class="menu-icon"><i class="ki-duotone ki-dollar fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i></span>
			<span class="menu-title">{{ __('Income & expenses') }}</span>
		</a>
	</div>
	<div class="menu-item">
		<a class="menu-link" href="{{ route('rental.reports.index') }}">
			<span class="menu-icon"><i class="ki-duotone ki-chart-line-up fs-2"><span class="path1"></span><span class="path2"></span></i></span>
			<span class="menu-title">{{ __('Reports') }}</span>
		</a>
	</div>
@endif
