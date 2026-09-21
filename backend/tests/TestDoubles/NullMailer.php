<?php

declare(strict_types=1);

namespace App\Tests\TestDoubles;

use App\Domain\Contact\MailerInterface;
use App\Entity\ContactSubmission;

/**
 * Mailer no-op para el entorno de test: evita llamadas reales a la API de Brevo.
 */
final class NullMailer implements MailerInterface
{
    public function sendContactNotification(ContactSubmission $submission): void
    {
        // no-op
    }
}
