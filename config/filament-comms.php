<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Platform Branding (Customized per app: CediPlus, Commisoft, CeeSend, SukuApp)
    |--------------------------------------------------------------------------
    */
    'brand_name' => env('COMMS_BRAND_NAME', env('APP_NAME', 'Commisoft')),
    'brand_logo' => env('COMMS_BRAND_LOGO', null),
    'primary_color' => env('COMMS_PRIMARY_COLOR', '#28bd0c'),
    'support_email' => env('COMMS_SUPPORT_EMAIL', env('MAIL_FROM_ADDRESS', 'support@commisoft.com')),
    'footer_address' => env('COMMS_FOOTER_ADDRESS', 'Accra, Ghana'),

    /*
    |--------------------------------------------------------------------------
    | Sender Identity
    |--------------------------------------------------------------------------
    */
    'from' => [
        'address' => env('COMMS_FROM_ADDRESS', env('MAIL_FROM_ADDRESS', 'hello@commisoft.com')),
        'name' => env('COMMS_FROM_NAME', env('APP_NAME', 'Commisoft')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Categories
    |--------------------------------------------------------------------------
    | Tier A categories are user-controllable (can unsubscribe).
    */
    'categories' => [
        'promotions' => [
            'label' => 'Promotions & Offers',
            'description' => 'Special discounts, plan upgrade offers, and partner promotions.',
            'default' => true,
        ],
        'newsletters' => [
            'label' => 'Newsletters',
            'description' => 'Monthly company news, industry insights, and customer stories.',
            'default' => true,
        ],
        'product_updates' => [
            'label' => 'Product Updates',
            'description' => 'New feature launches, improvements, and release notes.',
            'default' => true,
        ],
        'digests' => [
            'label' => 'Activity Digests',
            'description' => 'Weekly or monthly performance summaries for your account.',
            'default' => true,
        ],
        'onboarding_tips' => [
            'label' => 'Tips & Onboarding',
            'description' => 'Helpful guides and best practices to get the most out of the platform.',
            'default' => true,
        ],
        'surveys_feedback' => [
            'label' => 'Surveys & Feedback',
            'description' => 'Occasional feedback requests and beta program invitations.',
            'default' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue & Throttling
    |--------------------------------------------------------------------------
    */
    'queues' => [
        'bulk' => env('COMMS_BULK_QUEUE', 'emails-bulk'),
        'transactional' => env('COMMS_TRANSACTIONAL_QUEUE', 'emails-transactional'),
    ],
    'chunk_size' => 50,

    /*
    |--------------------------------------------------------------------------
    | Audience Resolver
    |--------------------------------------------------------------------------
    | Override this in each platform to expose app-specific user segments.
    */
    'audience_resolver' => \Commisoft\FilamentComms\Services\DefaultAudienceResolver::class,

    /*
    |--------------------------------------------------------------------------
    | Filament Navigation Group
    |--------------------------------------------------------------------------
    */
    'navigation_group' => 'Communications',
];
