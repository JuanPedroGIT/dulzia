<?php

namespace App\Application\Contact\SubmitContact;

use App\Domain\Contact\ContactRepositoryInterface;
use App\Domain\Contact\MailerInterface;
use App\Entity\ContactSubmission;
use Psr\Log\LoggerInterface;

final class SubmitContactHandler
{
    public function __construct(
        private ContactRepositoryInterface $repository,
        private MailerInterface $mailer,
        private LoggerInterface $logger,
    ) {}

    public function handle(SubmitContactCommand $command): ContactSubmission
    {
        $submission = new ContactSubmission(
            name: $command->name,
            email: $command->email,
            message: $command->message,
            phone: $command->phone,
            eventType: $command->eventType,
            ipAddress: $command->ipAddress,
        );

        $this->repository->save($submission);

        try {
            $this->mailer->sendContactNotification($submission);
            $submission->markEmailSent();
            $this->repository->save($submission);
        } catch (\Throwable $e) {
            // El mensaje queda guardado igualmente (fallo de email no fatal),
            // pero se registra para poder revisarlo/reenviarlo después.
            $this->logger->error('Fallo al enviar email de contacto', [
                'submission_id' => $submission->getId(),
                'email'         => $submission->getEmail(),
                'exception'     => $e,
            ]);
        }

        return $submission;
    }
}
