<?php

namespace App\Infrastructure\Email;

use App\Entity\ContactSubmission;

/**
 * Plantillas HTML de los emails de contacto (escapado incluido).
 * Separado del transporte: BrevoMailer solo envía.
 */
final class ContactMailRenderer
{
    public function renderNotification(ContactSubmission $s): string
    {
        return sprintf(
            '<h2>Nuevo contacto desde la web</h2>
            <p><strong>Nombre:</strong> %s</p>
            <p><strong>Email:</strong> %s</p>
            <p><strong>Teléfono:</strong> %s</p>
            <p><strong>Tipo de evento:</strong> %s</p>
            <p><strong>Mensaje:</strong></p>
            <blockquote>%s</blockquote>
            <p><em>Recibido el %s</em></p>',
            htmlspecialchars($s->getName()),
            htmlspecialchars($s->getEmail()),
            htmlspecialchars($s->getPhone() ?? '—'),
            htmlspecialchars($s->getEventType() ?? '—'),
            nl2br(htmlspecialchars($s->getMessage())),
            $s->getSubmittedAt()->format('d/m/Y H:i'),
        );
    }

    public function renderConfirmation(ContactSubmission $s): string
    {
        return sprintf(
            '<h2>¡Gracias por contactarnos, %s!</h2>
            <p>Hemos recibido tu mensaje y nos pondremos en contacto contigo lo antes posible.</p>
            <p>Si necesitas respuesta urgente, puedes llamarnos al <strong>+34 629 991 659</strong>.</p>
            <br>
            <p>Un saludo,<br><strong>Dulzia Salamanca Eventos</strong></p>',
            htmlspecialchars($s->getName()),
        );
    }
}
