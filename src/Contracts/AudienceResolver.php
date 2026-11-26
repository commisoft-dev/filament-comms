<?php

namespace Commisoft\FilamentComms\Contracts;

use Illuminate\Support\Collection;

interface AudienceResolver
{
    /**
     * Key-value map of available audience segment keys => human labels.
     *
     * @return array<string, string>
     */
    public function segments(): array;

    /**
     * Resolve deduplicated RecipientDto collection for the given campaign filter.
     *
     * @param  array<string, mixed>  $filter
     * @return Collection<int, RecipientDto>
     */
    public function resolveRecipients(array $filter): Collection;
}
