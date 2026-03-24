<?php

namespace App\Infrastructure\Email;

use App\Entity\ContactSubmission;

interface MailerInterface
{
    public function sendContactNotification(ContactSubmission $submission): void;
}
