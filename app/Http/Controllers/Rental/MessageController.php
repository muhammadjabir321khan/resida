<?php

namespace App\Http\Controllers\Rental;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rental\StoreRentalMessageRequest;
use App\Models\Lease;
use App\Models\MessageThread;
use App\Services\MessageThreadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function __construct(
        private MessageThreadService $threads,
    ) {}

    public function index(): View
    {
        $threads = MessageThread::query()
            ->with(['lease.property', 'rentalTenant', 'latestMessage'])
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->paginate(20);

        return view('rental.messages.index', compact('threads'));
    }

    public function show(MessageThread $thread): View
    {
        abort_if((int) $thread->user_id !== (int) auth()->id(), 403);

        $this->threads->markReadForLandlord($thread);

        $thread->load(['lease.property', 'lease.unit', 'rentalTenant', 'messages.sender']);

        return view('rental.messages.show', compact('thread'));
    }

    public function store(StoreRentalMessageRequest $request, MessageThread $thread): RedirectResponse
    {
        abort_if((int) $thread->user_id !== (int) auth()->id(), 403);

        $this->threads->sendMessage($thread, auth()->user(), $request->validated('body'));

        return redirect()->route('rental.messages.show', $thread);
    }

    public function startFromLease(Lease $lease): RedirectResponse
    {
        abort_if((int) $lease->user_id !== (int) auth()->id(), 403);

        $thread = $this->threads->findOrCreateForLease($lease);

        return redirect()->route('rental.messages.show', $thread);
    }
}
