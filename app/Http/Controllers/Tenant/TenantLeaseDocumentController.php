<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Lease;
use App\Models\LeaseDocument;
use App\Models\MessageThread;
use App\Services\TenantProfileResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TenantLeaseDocumentController extends Controller
{
    public function index(): View
    {
        $profile = TenantProfileResolver::forUser(auth()->user());
        abort_if($profile === null, 403);

        $leaseIds = Lease::withoutLandlordScope()
            ->where('tenant_id', $profile->id)
            ->pluck('id');

        $documents = LeaseDocument::withoutLandlordScope()
            ->whereIn('lease_id', $leaseIds)
            ->where('is_visible_to_tenant', true)
            ->with(['lease.property'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('tenant.documents.index', compact('documents', 'profile'));
    }

    public function download(int $documentId): StreamedResponse|RedirectResponse
    {
        $profile = TenantProfileResolver::forUser(auth()->user());
        abort_if($profile === null, 403);

        $document = LeaseDocument::withoutLandlordScope()->findOrFail($documentId);

        if (! $document->is_visible_to_tenant) {
            abort(403);
        }

        $allowed = Lease::withoutLandlordScope()
            ->where('id', $document->lease_id)
            ->where('tenant_id', $profile->id)
            ->exists();

        abort_if(! $allowed, 403);

        return Storage::disk('public')->download(
            $document->file_path,
            $document->original_filename,
        );
    }
}
