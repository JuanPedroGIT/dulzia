<?php

namespace App\Domain\Contact;

use App\Entity\ContactSubmission;

interface MailerInterface
{
    public function sendContactNotification(ContactSubmission $submission): void;
}
