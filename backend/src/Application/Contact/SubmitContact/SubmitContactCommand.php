<?php

namespace App\Application\Contact\SubmitContact;

final readonly class SubmitContactCommand
{
    public function __construct(
        public string $name,
        public string $email,
        public string $message,
        public ?string $phone = null,
        public ?string $eventType = null,
        public ?string $ipAddress = null,
    ) {}
}
