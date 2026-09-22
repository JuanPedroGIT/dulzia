<?php

namespace App\Application\Contact\ListMessages;

use App\Domain\Contact\ContactRepositoryInterface;

final class ListMessagesHandler
{
    public function __construct(
        private ContactRepositoryInterface $messages,
    ) {}

    public function handle(ListMessagesQuery $query): array
    {
        $limit  = max(1, $query->limit);
        $offset = ($query->page - 1) * $limit;
        $total  = $this->messages->countAll();

        return [
            'items' => array_map(
                static fn($m) => [
                    'id'           => $m->getId(),
                    'name'         => $m->getName(),
                    'email'        => $m->getEmail(),
                    'phone'        => $m->getPhone(),
                    'event_type'   => $m->getEventType(),
                    'submitted_at' => $m->getSubmittedAt()->format('c'),
                    'is_read'      => $m->isRead(),
                    'email_sent'   => $m->isEmailSent(),
                    'email_sent_at' => $m->getEmailSentAt()?->format('c'),
                ],
                $this->messages->findPage($offset, $limit)
            ),
            'page'        => $query->page,
            'limit'       => $limit,
            'totalPages'  => max(1, (int) ceil($total / $limit)),
            'total'       => $total,
            'unreadCount' => $this->messages->countUnread(),
        ];
    }
}
