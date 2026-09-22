<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Contact;

use App\Application\Contact\SubmitContact\SubmitContactCommand;
use App\Application\Contact\SubmitContact\SubmitContactHandler;
use App\Domain\Contact\ContactRepositoryInterface;
use App\Entity\ContactSubmission;
use App\Domain\Contact\MailerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class SubmitContactHandlerTest extends TestCase
{
    public function testSavesSubmissionAndMarksEmailAsSent(): void
    {
        $repository = $this->createMock(ContactRepositoryInterface::class);
        $repository->expects($this->exactly(2))
            ->method('save')
            ->with($this->callback(fn (ContactSubmission $s): bool => true));

        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->once())->method('sendContactNotification');

        $logger = $this->createMock(LoggerInterface::class);

        $result = (new SubmitContactHandler($repository, $mailer, $logger))->handle(new SubmitContactCommand(
            name: 'María',
            email: 'maria@example.com',
            message: 'Hola, quiero un presupuesto',
            phone: '+34 600 000 000',
            eventType: 'boda',
            ipAddress: '127.0.0.1',
        ));

        self::assertInstanceOf(ContactSubmission::class, $result);
        self::assertSame('María', $result->getName());
        self::assertSame('maria@example.com', $result->getEmail());
        self::assertSame('+34 600 000 000', $result->getPhone());
        self::assertSame('boda', $result->getEventType());
        self::assertTrue($result->isEmailSent());
    }

    public function testEmailFailureIsNonFatal(): void
    {
        $repository = $this->createMock(ContactRepositoryInterface::class);
        // Solo el guardado inicial: no se vuelve a guardar tras el fallo de email
        $repository->expects($this->once())->method('save');

        $mailer = $this->createMock(MailerInterface::class);
        $mailer->method('sendContactNotification')
            ->willThrowException(new \RuntimeException('Brevo caído'));

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->with(
                'Fallo al enviar email de contacto',
                $this->callback(fn (array $context): bool =>
                    isset($context['submission_id'], $context['exception'])
                    && $context['exception'] instanceof \RuntimeException
                )
            );

        $result = (new SubmitContactHandler($repository, $mailer, $logger))->handle(new SubmitContactCommand(
            name: 'María',
            email: 'maria@example.com',
            message: 'Hola',
        ));

        self::assertInstanceOf(ContactSubmission::class, $result);
        self::assertFalse($result->isEmailSent());
    }

    public function testOptionalFieldsDefaultToNull(): void
    {
        $repository = $this->createMock(ContactRepositoryInterface::class);
        // Dos guardados: inicial + tras marcar el email como enviado
        $repository->expects($this->exactly(2))->method('save');

        $mailer = $this->createMock(MailerInterface::class);

        $logger = $this->createMock(LoggerInterface::class);

        $result = (new SubmitContactHandler($repository, $mailer, $logger))->handle(new SubmitContactCommand(
            name: 'María',
            email: 'maria@example.com',
            message: 'Hola',
        ));

        self::assertNull($result->getPhone());
        self::assertNull($result->getEventType());
        // La entidad no expone getter de ipAddress (solo se persiste)
        $ip = (new \ReflectionProperty(ContactSubmission::class, 'ipAddress'))->getValue($result);
        self::assertNull($ip);
    }
}
