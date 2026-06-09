<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rental\StoreRentalMessageRequest;
use App\Models\MessageThread;
use App\Services\MessageThreadService;
use App\Services\TenantProfileResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TenantMessageController extends Controller
{
    public function __construct(
        private MessageThreadService $threads,
    ) {}

    public function index(): View
    {
        $profile = TenantProfileResolver::forUser(auth()->user());
        abort_if($profile === null, 403);

        $threads = MessageThread::withoutLandlordScope()
            ->where('rental_tenant_id', $profile->id)
            ->with(['lease.property', 'latestMessage'])
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->paginate(20);

        return view('tenant.messages.index', compact('threads', 'profile'));
    }

    public function show(MessageThread $thread): View
    {
        $profile = TenantProfileResolver::forUser(auth()->user());
        abort_if($profile === null || ! $this->threads->tenantCanAccess($thread, $profile), 403);

        $this->threads->markReadForTenant($thread);

        $thread->load(['lease.property', 'lease.unit', 'messages.sender']);

        return view('tenant.messages.show', compact('thread', 'profile'));
    }

    public function store(StoreRentalMessageRequest $request, MessageThread $thread): RedirectResponse
    {
        $profile = TenantProfileResolver::forUser(auth()->user());
        abort_if($profile === null || ! $this->threads->tenantCanAccess($thread, $profile), 403);

        $this->threads->sendMessage($thread, auth()->user(), $request->validated('body'));

        return redirect()->route('tenant.messages.show', $thread);
    }
}
