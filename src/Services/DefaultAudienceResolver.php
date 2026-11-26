<?php

namespace Commisoft\FilamentComms\Services;

use Commisoft\FilamentComms\Contracts\AudienceResolver;
use Commisoft\FilamentComms\Contracts\RecipientDto;
use Illuminate\Support\Collection;

class DefaultAudienceResolver implements AudienceResolver
{
    public function segments(): array
    {
        return [
            'all_users' => 'All Registered Users',
            'verified_users' => 'Email-Verified Users',
            'manual' => 'Manual Email List Only',
        ];
    }

    public function resolveRecipients(array $filter): Collection
    {
        $segment = $filter['segment'] ?? 'all_users';
        $recipients = collect();

        if ($segment !== 'manual') {
            $userModel = config('auth.providers.users.model', 'App\\Models\\User');
            if (class_exists($userModel)) {
                $query = $userModel::query()->whereNotNull('email');

                if ($segment === 'verified_users') {
                    $query->whereNotNull('email_verified_at');
                }

                $query->get()->each(function ($user) use ($recipients, $userModel) {
                    $recipients->push(new RecipientDto(
                        email: strtolower(trim($user->email)),
                        name: (string) ($user->name ?? ''),
                        id: $user->id,
                        type: $userModel,
                    ));
                });
            }
        }

        if (!empty($filter['manual_emails'])) {
            $lines = preg_split('/[\r\n,;]+/', (string) $filter['manual_emails'], -1, PREG_SPLIT_NO_EMPTY);
            foreach ($lines as $rawEmail) {
                $email = strtolower(trim($rawEmail));
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $recipients->push(new RecipientDto(email: $email, name: ''));
                }
            }
        }

        return $recipients->unique('email')->values();
    }
}
