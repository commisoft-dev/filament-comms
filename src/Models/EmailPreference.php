<?php

namespace Commisoft\FilamentComms\Models;

use Illuminate\Database\Eloquent\Model;

class EmailPreference extends Model
{
    protected $table = 'comms_email_preferences';

    protected $fillable = [
        'email',
        'user_id',
        'preferences',
        'unsubscribed_all',
        'unsubscribed_at',
        'unsubscribe_reason',
    ];

    protected $casts = [
        'preferences' => 'array',
        'unsubscribed_all' => 'boolean',
        'unsubscribed_at' => 'datetime',
    ];

    public function setEmailAttribute(string $value): void
    {
        $this->attributes['email'] = strtolower(trim($value));
    }
}
