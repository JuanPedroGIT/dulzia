<?php

namespace App\Application\Contact\DeleteMessage;

use App\Domain\Contact\ContactRepositoryInterface;
use App\Domain\Shared\NotFoundException;

final class DeleteMessageHandler
{
    public function __construct(
        private ContactRepositoryInterface $messages,
    ) {}

    public function handle(DeleteMessageCommand $command): void
    {
        $message = $this->messages->find($command->id);

        if ($message === null) {
            throw new NotFoundException('Mensaje no encontrado');
        }

        $this->messages->remove($message);
    }
}
