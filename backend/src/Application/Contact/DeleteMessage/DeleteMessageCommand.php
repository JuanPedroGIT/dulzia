<?php

namespace App\Application\Contact\DeleteMessage;

final readonly class DeleteMessageCommand
{
    public function __construct(
        public string $id,
    ) {}
}
