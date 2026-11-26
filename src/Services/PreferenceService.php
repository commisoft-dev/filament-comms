<?php

namespace Commisoft\FilamentComms\Services;

use Commisoft\FilamentComms\Models\EmailPreference;
use Commisoft\FilamentComms\Models\EmailSuppression;
use Illuminate\Support\Facades\URL;

class PreferenceService
{
    public function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    public function canSend(string $email, string $category, bool $isMandatory = false): bool
    {
        $normalized = $this->normalizeEmail($email);

        if (EmailSuppression::where('email', $normalized)->exists()) {
            return false;
        }

        if ($isMandatory) {
            return true;
        }

        $pref = EmailPreference::where('email', $normalized)->first();
        if (!$pref) {
            return (bool) config("filament-comms.categories.{$category}.default", true);
        }

        if ($pref->unsubscribed_all) {
            return false;
        }

        $prefs = $pref->preferences ?? [];
        if (array_key_exists($category, $prefs)) {
            return (bool) $prefs[$category];
        }

        return (bool) config("filament-comms.categories.{$category}.default", true);
    }

    public function getPreferencesForEmail(string $email): array
    {
        $normalized = $this->normalizeEmail($email);
        $pref = EmailPreference::where('email', $normalized)->first();
        $categories = config('filament-comms.categories', []);

        $result = [];
        foreach ($categories as $key => $meta) {
            $default = (bool) ($meta['default'] ?? true);
            $result[$key] = $pref && !$pref->unsubscribed_all
                ? (bool) ($pref->preferences[$key] ?? $default)
                : ($pref?->unsubscribed_all ? false : $default);
        }

        return [
            'preferences' => $result,
            'unsubscribed_all' => (bool) ($pref?->unsubscribed_all ?? false),
            'unsubscribe_reason' => $pref?->unsubscribe_reason,
        ];
    }

    public function updatePreferences(string $email, array $categoryMap, bool $unsubscribedAll = false, ?string $reason = null, ?int $userId = null): EmailPreference
    {
        $normalized = $this->normalizeEmail($email);
        $categories = array_keys(config('filament-comms.categories', []));

        $clean = [];
        foreach ($categories as $cat) {
            $clean[$cat] = $unsubscribedAll ? false : (bool) ($categoryMap[$cat] ?? false);
        }

        return EmailPreference::updateOrCreate(
            ['email' => $normalized],
            [
                'user_id' => $userId,
                'preferences' => $clean,
                'unsubscribed_all' => $unsubscribedAll,
                'unsubscribed_at' => $unsubscribedAll ? now() : null,
                'unsubscribe_reason' => $reason,
            ]
        );
    }

    public function unsubscribeCategory(string $email, string $category, ?string $reason = null): EmailPreference
    {
        $normalized = $this->normalizeEmail($email);
        $current = $this->getPreferencesForEmail($normalized);
        $prefs = $current['preferences'];
        $prefs[$category] = false;

        return $this->updatePreferences($normalized, $prefs, false, $reason);
    }

    public function signedPreferencesUrl(string $email, ?string $category = null): string
    {
        return URL::signedRoute('filament-comms.preferences', array_filter([
            'email' => $this->normalizeEmail($email),
            'category' => $category,
        ]));
    }

    public function signedOneClickUrl(string $email, string $category): string
    {
        return URL::signedRoute('filament-comms.one-click', [
            'email' => $this->normalizeEmail($email),
            'category' => $category,
        ]);
    }
}
