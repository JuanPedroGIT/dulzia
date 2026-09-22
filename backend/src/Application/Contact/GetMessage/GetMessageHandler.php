<?php

namespace App\Application\Contact\GetMessage;

use App\Domain\Contact\ContactRepositoryInterface;

final class GetMessageHandler
{
    public function __construct(
        private ContactRepositoryInterface $messages,
    ) {}

    public function handle(GetMessageQuery $query): ?array
    {
        $message = $this->messages->find($query->id);

        if ($message === null) {
            return null;
        }

        return [
            'id'           => $message->getId(),
            'name'         => $message->getName(),
            'email'        => $message->getEmail(),
            'phone'        => $message->getPhone(),
            'event_type'   => $message->getEventType(),
            'message'      => $message->getMessage(),
            'ip_address'   => $message->getIpAddress(),
            'submitted_at' => $message->getSubmittedAt()->format('c'),
            'is_read'      => $message->isRead(),
            'read_at'      => $message->getReadAt()?->format('c'),
            'email_sent'   => $message->isEmailSent(),
            'email_sent_at' => $message->getEmailSentAt()?->format('c'),
        ];
    }
}
