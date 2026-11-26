<?php

namespace Commisoft\FilamentComms\Models;

use Illuminate\Database\Eloquent\Model;

class EmailSuppression extends Model
{
    protected $table = 'comms_email_suppressions';

    protected $fillable = [
        'email',
        'reason',
        'provider_payload',
    ];

    protected $casts = [
        'provider_payload' => 'array',
    ];

    public function setEmailAttribute(string $value): void
    {
        $this->attributes['email'] = strtolower(trim($value));
    }
}
