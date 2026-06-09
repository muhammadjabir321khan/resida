<?php

namespace App\Http\Controllers\Rental;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rental\StoreLeaseDocumentRequest;
use App\Models\Lease;
use App\Models\LeaseDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeaseDocumentController extends Controller
{
    public function store(StoreLeaseDocumentRequest $request, Lease $lease): RedirectResponse
    {
        $file = $request->file('file');
        $path = $file->store('lease-documents/'.$lease->id, 'public');

        LeaseDocument::create([
            'lease_id' => $lease->id,
            'title' => $request->validated('title'),
            'category' => $request->validated('category'),
            'file_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'is_visible_to_tenant' => $request->boolean('is_visible_to_tenant', true),
        ]);

        if ($request->validated('category') === LeaseDocument::CATEGORY_LEASE && ! $lease->document_path) {
            $lease->update(['document_path' => $path]);
        }

        return redirect()
            ->route('rental.leases.edit', $lease)
            ->with('swal', [
                'icon' => 'success',
                'title' => __('Uploaded'),
                'text' => __('Document added to this lease.'),
            ]);
    }

    public function destroy(Lease $lease, LeaseDocument $document): RedirectResponse
    {
        abort_if((int) $document->lease_id !== (int) $lease->id, 404);

        if ($lease->document_path === $document->file_path) {
            $lease->update(['document_path' => null]);
        }

        $document->deleteFile();
        $document->delete();

        return redirect()
            ->route('rental.leases.edit', $lease)
            ->with('swal', [
                'icon' => 'success',
                'title' => __('Deleted'),
                'text' => __('Document removed.'),
            ]);
    }

    public function download(Lease $lease, LeaseDocument $document): StreamedResponse
    {
        abort_if((int) $document->lease_id !== (int) $lease->id, 404);

        return Storage::disk('public')->download(
            $document->file_path,
            $document->original_filename,
        );
    }
}
