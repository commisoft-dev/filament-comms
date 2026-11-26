<?php

namespace Commisoft\FilamentComms\Services;

use Commisoft\FilamentComms\Contracts\AudienceResolver;
use Commisoft\FilamentComms\Contracts\RecipientDto;
use Commisoft\FilamentComms\Jobs\SendCampaignEmailJob;
use Commisoft\FilamentComms\Mail\CampaignMailable;
use Commisoft\FilamentComms\Models\EmailCampaign;
use Commisoft\FilamentComms\Models\EmailDelivery;
use Illuminate\Support\Facades\Mail;

class CampaignDispatcher
{
    public function __construct(
        protected AudienceResolver $audienceResolver,
        protected PreferenceService $preferenceService,
    ) {}

    public function countEligibleRecipients(EmailCampaign $campaign): array
    {
        $recipients = $this->audienceResolver->resolveRecipients($campaign->audience_filter ?? []);
        $eligible = 0;
        $skipped = 0;

        foreach ($recipients as $recipient) {
            if ($this->preferenceService->canSend($recipient->email, $campaign->category)) {
                $eligible++;
            } else {
                $skipped++;
            }
        }

        return [
            'total' => $recipients->count(),
            'eligible' => $eligible,
            'skipped' => $skipped,
        ];
    }

    public function dispatch(EmailCampaign $campaign): int
    {
        $recipients = $this->audienceResolver->resolveRecipients($campaign->audience_filter ?? []);

        $campaign->update([
            'status' => 'sending',
            'started_at' => $campaign->started_at ?? now(),
            'total_recipients' => $recipients->count(),
        ]);

        $queuedCount = 0;
        $skippedCount = 0;
        $bulkQueue = config('filament-comms.queues.bulk', 'emails-bulk');

        foreach ($recipients as $recipient) {
            // Idempotency: skip if already recorded for this campaign
            if (EmailDelivery::where('campaign_id', $campaign->id)->where('email', $recipient->email)->exists()) {
                continue;
            }

            $canSend = $this->preferenceService->canSend($recipient->email, $campaign->category);

            $delivery = EmailDelivery::create([
                'campaign_id' => $campaign->id,
                'recipient_type' => $recipient->type,
                'recipient_id' => $recipient->id,
                'email' => $recipient->email,
                'category' => $campaign->category,
                'status' => $canSend ? 'queued' : 'skipped_unsubscribed',
            ]);

            if (!$canSend) {
                $skippedCount++;
                continue;
            }

            SendCampaignEmailJob::dispatch($delivery->id, [
                'name' => $recipient->name,
                'meta' => $recipient->meta,
            ])->onQueue($bulkQueue);

            $queuedCount++;
        }

        $campaign->increment('skipped_count', $skippedCount);

        if ($queuedCount === 0) {
            $campaign->update([
                'status' => 'sent',
                'completed_at' => now(),
            ]);
        }

        return $queuedCount;
    }

    public function sendTest(EmailCampaign $campaign, string $testEmail, string $testName = 'Admin'): void
    {
        $recipient = new RecipientDto(
            email: strtolower(trim($testEmail)),
            name: $testName,
            meta: ['company_name' => config('filament-comms.brand_name')],
        );

        $rendered = $this->renderForRecipient($campaign, $recipient);

        Mail::to($recipient->email)->send(new CampaignMailable(
            subjectLine: '[TEST] ' . $rendered['subject'],
            preheader: $campaign->preheader,
            bodyHtml: $rendered['body_html'],
            category: $campaign->category,
            preferencesUrl: $this->preferenceService->signedPreferencesUrl($recipient->email, $campaign->category),
            oneClickUrl: $this->preferenceService->signedOneClickUrl($recipient->email, $campaign->category),
        ));
    }

    public function renderForRecipient(EmailCampaign $campaign, RecipientDto $recipient): array
    {
        $preferencesUrl = $this->preferenceService->signedPreferencesUrl($recipient->email, $campaign->category);

        $tokens = array_merge([
            '{{name}}' => e($recipient->name ?: 'there'),
            '{{first_name}}' => e($recipient->firstName()),
            '{{email}}' => e($recipient->email),
            '{{brand_name}}' => e(config('filament-comms.brand_name')),
            '{{preferences_url}}' => $preferencesUrl,
            '{{unsubscribe_url}}' => $preferencesUrl,
        ], collect($recipient->meta)->mapWithKeys(fn($v, $k) => ["{{{$k}}}" => e((string) $v)])->all());

        return [
            'subject' => strtr($campaign->subject, $tokens),
            'body_html' => strtr($campaign->body_html, $tokens),
        ];
    }
}
