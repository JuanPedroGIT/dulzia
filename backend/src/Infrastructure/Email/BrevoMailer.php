<?php

namespace App\Infrastructure\Email;

use App\Domain\Contact\MailerInterface;
use App\Domain\Settings\ContactRecipientResolver;
use App\Entity\ContactSubmission;

/**
 * Transporte de email contra la API REST de Brevo.
 * El renderizado de las plantillas vive en ContactMailRenderer y el
 * destinatario de la notificación en ContactRecipientResolver.
 */
final class BrevoMailer implements MailerInterface
{
    private const API_URL = 'https://api.brevo.com/v3/smtp/email';
    private const CONNECT_TIMEOUT = 5;
    private const TIMEOUT = 15;

    public function __construct(
        private string $apiKey,
        private ContactMailRenderer $renderer,
        // Destinatario de la notificación al negocio: se configura desde el
        // panel y, si no hay nada, cae al valor del .env
        private ContactRecipientResolver $recipients,
        // Remitente de la notificación
        private string $fromEmail,
        private string $fromName,
        // Remitente de la confirmación al usuario
        private string $confirmFromEmail,
        private string $confirmFromName,
    ) {}

    public function sendContactNotification(ContactSubmission $submission): void
    {
        // Se resuelve en cada envío: así un cambio en el panel se aplica sin
        // reiniciar el contenedor.
        $recipient = $this->recipients->resolve();

        $this->send([
            'sender'  => ['name' => $this->fromName, 'email' => $this->fromEmail],
            'to'      => [['email' => $recipient->email, 'name' => $recipient->name]],
            'subject' => '📩 Nuevo mensaje de ' . $submission->getName(),
            'htmlContent' => $this->renderer->renderNotification($submission),
        ]);

        $this->send([
            'sender'  => ['name' => $this->confirmFromName, 'email' => $this->confirmFromEmail],
            'to'      => [['email' => $submission->getEmail(), 'name' => $submission->getName()]],
            'subject' => '¡Hemos recibido tu mensaje!',
            'htmlContent' => $this->renderer->renderConfirmation($submission),
        ]);
    }

    private function send(array $payload): void
    {
        $ch = curl_init(self::API_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'Content-Type: application/json',
                'api-key: ' . $this->apiKey,
            ],
            // Sin timeout, una API caída colgaría la petición PHP
            CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT,
            CURLOPT_TIMEOUT        => self::TIMEOUT,
        ]);

        $response = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status >= 400) {
            throw new \RuntimeException('Brevo API error: ' . $response);
        }
    }
}
