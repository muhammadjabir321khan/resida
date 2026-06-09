@extends('layouts.main')
@section('title', __('My documents'))
@section('content')
	<div class="card">
		<div class="card-header border-0 pt-6">
			<div class="card-title"><h2 class="fw-bold m-0">{{ __('Lease documents') }}</h2></div>
		</div>
		<div class="card-body py-4">
			<div class="table-responsive">
				<table class="table align-middle table-row-dashed gy-4">
					<thead>
						<tr class="text-muted fw-bold fs-7 text-uppercase">
							<th>{{ __('Title') }}</th>
							<th>{{ __('Category') }}</th>
							<th>{{ __('Property') }}</th>
							<th>{{ __('Uploaded') }}</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						@forelse ($documents as $document)
							<tr>
								<td class="fw-semibold">{{ $document->title }}</td>
								<td>{{ $document->categoryLabel() }}</td>
								<td>{{ $document->lease?->property?->name ?? '—' }}</td>
								<td>{{ $document->created_at?->format('Y-m-d') }}</td>
								<td class="text-end">
									<a href="{{ route('tenant.documents.download', $document->id) }}" class="btn btn-sm btn-light-primary">{{ __('Download') }}</a>
								</td>
							</tr>
						@empty
							<tr><td colspan="5" class="text-muted">{{ __('No documents shared with you yet.') }}</td></tr>
						@endforelse
					</tbody>
				</table>
			</div>
			{{ $documents->links() }}
		</div>
	</div>
@endsection
