<?php

namespace Commisoft\FilamentComms\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class CampaignMailable extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $subjectLine,
        public ?string $preheader,
        public string $bodyHtml,
        public string $category,
        public string $preferencesUrl,
        public string $oneClickUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('filament-comms.from.address'),
                config('filament-comms.from.name')
            ),
            subject: $this->subjectLine,
        );
    }

    public function headers(): Headers
    {
        return new Headers(
            text: [
                'List-Unsubscribe' => "<{$this->oneClickUrl}>",
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
                'X-Comms-Category' => $this->category,
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'filament-comms::emails.campaign',
        );
    }
}
