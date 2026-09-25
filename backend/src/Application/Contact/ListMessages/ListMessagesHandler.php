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

        // Dos consultas y no tres: los leídos salen de restar. `total` es el del
        // filtro activo (es lo que pagina la tabla) y `counts` lleva los tres
        // para las pestañas del panel.
        $all    = $this->messages->countAll();
        $unread = $this->messages->countUnread();
        $read   = $all - $unread;

        $total = match ($query->filter) {
            ListMessagesQuery::UNREAD => $unread,
            ListMessagesQuery::READ => $read,
            default => $all,
        };

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
                $this->messages->findPage($offset, $limit, $query->isReadFilter())
            ),
            'page'        => $query->page,
            'limit'       => $limit,
            'totalPages'  => max(1, (int) ceil($total / $limit)),
            'total'       => $total,
            'filter'      => $query->filter,
            'counts'      => [
                'all'    => $all,
                'unread' => $unread,
                'read'   => $read,
            ],
        ];
    }
}
