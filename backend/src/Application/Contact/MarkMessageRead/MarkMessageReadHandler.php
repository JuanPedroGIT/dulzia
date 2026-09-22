<?php

namespace App\Application\Contact\MarkMessageRead;

use App\Domain\Contact\ContactRepositoryInterface;
use App\Domain\Shared\NotFoundException;

final class MarkMessageReadHandler
{
    public function __construct(
        private ContactRepositoryInterface $messages,
    ) {}

    public function handle(MarkMessageReadCommand $command): void
    {
        $message = $this->messages->find($command->id);

        if ($message === null) {
            throw new NotFoundException('Mensaje no encontrado');
        }

        if ($command->read) {
            $message->markRead();
        } else {
            $message->markUnread();
        }

        $this->messages->save($message);
    }
}
