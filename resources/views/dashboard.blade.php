@extends('layouts.main')

@section('title', __('Dashboard'))

@section('content')
	@if (session('status'))
		<div class="alert alert-success mb-5">{{ session('status') }}</div>
	@endif
	@if ($errors->any())
		<div class="alert alert-danger mb-5">
			<ul class="mb-0 ps-4">
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
	@endif

	@switch($dashboardRole ?? 'landlord')
		@case('admin')
			@include('dashboards.admin')
			@break
		@case('tenant')
			@include('dashboards.tenant')
			@break
		@default
			@include('dashboards.landlord')
	@endswitch
@endsection

@push('scripts')
	@include('dashboards.charts')
@endpush
