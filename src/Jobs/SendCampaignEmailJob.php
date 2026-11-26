<?php

namespace Commisoft\FilamentComms\Jobs;

use Commisoft\FilamentComms\Contracts\RecipientDto;
use Commisoft\FilamentComms\Mail\CampaignMailable;
use Commisoft\FilamentComms\Models\EmailDelivery;
use Commisoft\FilamentComms\Services\CampaignDispatcher;
use Commisoft\FilamentComms\Services\PreferenceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendCampaignEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public int $deliveryId,
        public array $recipientData = [],
    ) {}

    public function handle(CampaignDispatcher $dispatcher, PreferenceService $preferences): void
    {
        $delivery = EmailDelivery::with('campaign')->find($this->deliveryId);
        if (!$delivery || !$delivery->campaign) {
            return;
        }

        $campaign = $delivery->campaign;

        if (in_array($campaign->status, ['paused', 'cancelled'], true)) {
            return;
        }

        if ($delivery->status !== 'queued') {
            return;
        }

        if (!$preferences->canSend($delivery->email, $campaign->category)) {
            $delivery->update(['status' => 'skipped_unsubscribed']);
            $campaign->increment('skipped_count');
            $this->markCompletedIfDone($campaign);
            return;
        }

        try {
            $recipient = new RecipientDto(
                email: $delivery->email,
                name: (string) ($this->recipientData['name'] ?? ''),
                id: $delivery->recipient_id,
                type: $delivery->recipient_type,
                meta: (array) ($this->recipientData['meta'] ?? []),
            );

            $rendered = $dispatcher->renderForRecipient($campaign, $recipient);

            Mail::to($delivery->email)->send(new CampaignMailable(
                subjectLine: $rendered['subject'],
                preheader: $campaign->preheader,
                bodyHtml: $rendered['body_html'],
                category: $campaign->category,
                preferencesUrl: $preferences->signedPreferencesUrl($delivery->email, $campaign->category),
                oneClickUrl: $preferences->signedOneClickUrl($delivery->email, $campaign->category),
            ));

            $delivery->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);
            $campaign->increment('sent_count');
        } catch (\Throwable $e) {
            $delivery->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            $campaign->increment('failed_count');
        }

        $this->markCompletedIfDone($campaign);
    }

    protected function markCompletedIfDone($campaign): void
    {
        $remaining = EmailDelivery::where('campaign_id', $campaign->id)
            ->where('status', 'queued')
            ->exists();

        if (!$remaining && $campaign->status === 'sending') {
            $campaign->update([
                'status' => 'sent',
                'completed_at' => now(),
            ]);
        }
    }
}
