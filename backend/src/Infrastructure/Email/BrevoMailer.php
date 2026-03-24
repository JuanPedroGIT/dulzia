<?php

namespace App\Infrastructure\Email;

use App\Entity\ContactSubmission;

final class BrevoMailer implements MailerInterface
{
    private const API_URL = 'https://api.brevo.com/v3/smtp/email';

    public function __construct(
        private string $apiKey,
        private string $toEmail = 'info@dulziasalamancaeventos.com',
        private string $toName = 'Dulzia Salamanca Eventos',
    ) {}

    public function sendContactNotification(ContactSubmission $submission): void
    {
        $this->send([
            'sender'  => ['name' => 'Web Dulzia Salamanca', 'email' => 'noreply@dulziasalamancaeventos.com'],
            'to'      => [['email' => $this->toEmail, 'name' => $this->toName]],
            'subject' => '📩 Nuevo mensaje de ' . $submission->getName(),
            'htmlContent' => $this->buildNotificationHtml($submission),
        ]);

        $this->send([
            'sender'  => ['name' => 'Dulzia Salamanca Eventos', 'email' => 'noreply@dulziasalamancaeventos.com'],
            'to'      => [['email' => $submission->getEmail(), 'name' => $submission->getName()]],
            'subject' => '¡Hemos recibido tu mensaje!',
            'htmlContent' => $this->buildConfirmationHtml($submission),
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
        ]);

        $response = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status >= 400) {
            throw new \RuntimeException('Brevo API error: ' . $response);
        }
    }

    private function buildNotificationHtml(ContactSubmission $s): string
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

    private function buildConfirmationHtml(ContactSubmission $s): string
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
