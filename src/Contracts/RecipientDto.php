<?php

namespace Commisoft\FilamentComms\Contracts;

class RecipientDto
{
    public function __construct(
        public readonly string $email,
        public readonly string $name = '',
        public readonly ?int $id = null,
        public readonly ?string $type = null,
        public readonly array $meta = [],
    ) {}

    public function firstName(): string
    {
        $trimmed = trim($this->name);
        if ($trimmed === '') {
            return 'there';
        }
        return explode(' ', $trimmed)[0];
    }
}
