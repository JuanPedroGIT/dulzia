<?php

namespace App\Application\Contact\SubmitContact;

use App\Domain\Contact\ContactRepositoryInterface;
use App\Entity\ContactSubmission;
use App\Infrastructure\Email\MailerInterface;

final class SubmitContactHandler
{
    public function __construct(
        private ContactRepositoryInterface $repository,
        private MailerInterface $mailer,
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
        } catch (\Throwable) {
            // Submission is saved; email failure is non-fatal
        }

        return $submission;
    }
}
