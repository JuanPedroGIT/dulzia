<?php

namespace App\Domain\Contact;

use App\Entity\ContactSubmission;

interface ContactRepositoryInterface
{
    public function save(ContactSubmission $submission): void;
}
