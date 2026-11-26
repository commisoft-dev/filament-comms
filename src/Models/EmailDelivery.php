<?php

namespace Commisoft\FilamentComms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailDelivery extends Model
{
    protected $table = 'comms_email_deliveries';

    protected $fillable = [
        'campaign_id',
        'recipient_type',
        'recipient_id',
        'email',
        'category',
        'status',
        'provider_message_id',
        'error_message',
        'sent_at',
        'opened_at',
        'clicked_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'campaign_id');
    }
}
