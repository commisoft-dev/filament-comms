<?php

namespace Commisoft\FilamentComms\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $table = 'comms_email_templates';

    protected $fillable = [
        'name',
        'slug',
        'category',
        'subject',
        'preheader',
        'body_html',
        'variables',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];
}
