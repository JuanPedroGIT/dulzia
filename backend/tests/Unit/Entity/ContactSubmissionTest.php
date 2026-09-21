<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\ContactSubmission;
use PHPUnit\Framework\TestCase;

final class ContactSubmissionTest extends TestCase
{
    public function testConstructorDefaults(): void
    {
        $submission = new ContactSubmission('María', 'maria@example.com', 'Hola');

        self::assertMatchesRegularExpression('/^[0-9a-f]{32}$/', $submission->getId());
        self::assertNull($submission->getPhone());
        self::assertNull($submission->getEventType());
        // La entidad no expone getter de ipAddress (solo se persiste)
        $ip = (new \ReflectionProperty(ContactSubmission::class, 'ipAddress'))->getValue($submission);
        self::assertNull($ip);
        self::assertFalse($submission->isEmailSent());
        self::assertInstanceOf(\DateTimeImmutable::class, $submission->getSubmittedAt());
    }

    public function testMarkEmailSent(): void
    {
        $submission = new ContactSubmission('María', 'maria@example.com', 'Hola');
        self::assertFalse($submission->isEmailSent());

        $submission->markEmailSent();

        self::assertTrue($submission->isEmailSent());
    }
}
