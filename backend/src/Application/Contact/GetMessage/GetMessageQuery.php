<?php

namespace App\Application\Contact\GetMessage;

final readonly class GetMessageQuery
{
    public function __construct(
        public string $id,
    ) {}
}
