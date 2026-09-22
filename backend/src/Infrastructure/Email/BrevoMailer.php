<?php

namespace App\Infrastructure\Email;

use App\Domain\Contact\MailerInterface;
use App\Entity\ContactSubmission;

/**
 * Transporte de email contra la API REST de Brevo.
 * El renderizado de las plantillas vive en ContactMailRenderer.
 */
final class BrevoMailer implements MailerInterface
{
    private const API_URL = 'https://api.brevo.com/v3/smtp/email';
    private const CONNECT_TIMEOUT = 5;
    private const TIMEOUT = 15;

    public function __construct(
        private string $apiKey,
        private ContactMailRenderer $renderer,
        private string $toEmail = 'salumvi@gmail.com',
        private string $toName = 'Dulzia Salamanca Eventos',
    ) {}

    public function sendContactNotification(ContactSubmission $submission): void
    {
        $this->send([
            'sender'  => ['name' => 'Web Dulzia Salamanca', 'email' => 'salumvi@gmail.com'],
            'to'      => [['email' => $this->toEmail, 'name' => $this->toName]],
            'subject' => '📩 Nuevo mensaje de ' . $submission->getName(),
            'htmlContent' => $this->renderer->renderNotification($submission),
        ]);

        $this->send([
            'sender'  => ['name' => 'Dulzia Salamanca Eventos', 'email' => 'noreply@dulziasalamancaeventos.com'],
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
