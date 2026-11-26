<?php

namespace Commisoft\FilamentComms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailCampaign extends Model
{
    protected $table = 'comms_email_campaigns';

    protected $fillable = [
        'template_id',
        'title',
        'category',
        'subject',
        'preheader',
        'body_html',
        'audience_filter',
        'status',
        'scheduled_at',
        'started_at',
        'completed_at',
        'total_recipients',
        'sent_count',
        'failed_count',
        'skipped_count',
        'created_by',
    ];

    protected $casts = [
        'audience_filter' => 'array',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(EmailDelivery::class, 'campaign_id');
    }
}
