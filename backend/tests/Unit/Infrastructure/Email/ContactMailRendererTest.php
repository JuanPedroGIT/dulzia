<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Email;

use App\Entity\ContactSubmission;
use App\Infrastructure\Email\ContactMailRenderer;
use PHPUnit\Framework\TestCase;

final class ContactMailRendererTest extends TestCase
{
    private function submission(): ContactSubmission
    {
        return new ContactSubmission(
            name: '<script>alert("x")</script>',
            email: 'maria@example.com',
            message: "Línea 1\nLínea 2",
            phone: '+34 600 000 000',
            eventType: 'boda',
        );
    }

    public function testNotificationEscapesUserInput(): void
    {
        $html = (new ContactMailRenderer())->renderNotification($this->submission());

        self::assertStringContainsString('maria@example.com', $html);
        self::assertStringContainsString('+34 600 000 000', $html);
        self::assertStringContainsString('boda', $html);
        self::assertStringNotContainsString('<script>', $html, 'El nombre debe ir escapado');
        self::assertStringContainsString('&lt;script&gt;', $html);
        // nl2br aplicado al mensaje
        self::assertStringContainsString('Línea 1<br />', $html);
    }

    public function testConfirmationEscapesNameAndIncludesPhone(): void
    {
        $html = (new ContactMailRenderer())->renderConfirmation($this->submission());

        self::assertStringNotContainsString('<script>', $html);
        self::assertStringContainsString('&lt;script&gt;', $html);
        self::assertStringContainsString('+34 629 991 659', $html);
    }
}
