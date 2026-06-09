<?php

namespace App\Mail;

use App\Models\Message;
use App\Models\MessageThread;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewRentalMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public MessageThread $thread,
        public Message $message,
        public string $recipientName,
        public string $conversationUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('[:app] New message: :subject', [
                'app' => config('app.name', 'Residia'),
                'subject' => $this->thread->displaySubject(),
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.new-rental-message-html',
            with: [
                'recipientName' => $this->recipientName,
                'subject' => $this->thread->displaySubject(),
                'bodyPreview' => \Illuminate\Support\Str::limit($this->message->body, 280),
                'conversationUrl' => $this->conversationUrl,
            ],
        );
    }
}
