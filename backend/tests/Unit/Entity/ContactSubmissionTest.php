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
        self::assertNull($submission->getIpAddress());
        self::assertFalse($submission->isEmailSent());
        self::assertNull($submission->getEmailSentAt());
        self::assertFalse($submission->isRead());
        self::assertNull($submission->getReadAt());
        self::assertInstanceOf(\DateTimeImmutable::class, $submission->getSubmittedAt());
    }

    public function testMarkEmailSent(): void
    {
        $submission = new ContactSubmission('María', 'maria@example.com', 'Hola');
        self::assertFalse($submission->isEmailSent());

        $submission->markEmailSent();

        self::assertTrue($submission->isEmailSent());
        self::assertInstanceOf(\DateTimeImmutable::class, $submission->getEmailSentAt());
    }

    public function testMarkReadAndUnread(): void
    {
        $submission = new ContactSubmission('María', 'maria@example.com', 'Hola');

        $submission->markRead();
        self::assertTrue($submission->isRead());
        self::assertInstanceOf(\DateTimeImmutable::class, $submission->getReadAt());

        $submission->markUnread();
        self::assertFalse($submission->isRead());
        self::assertNull($submission->getReadAt());
    }
}
