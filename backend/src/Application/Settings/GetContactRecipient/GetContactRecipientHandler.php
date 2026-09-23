<?php

namespace App\Application\Settings\GetContactRecipient;

use App\Domain\Settings\ContactRecipientResolver;

final class GetContactRecipientHandler
{
    public function __construct(
        private ContactRecipientResolver $recipients,
    ) {}

    public function handle(GetContactRecipientQuery $query): array
    {
        $recipient = $this->recipients->resolve();

        // Se devuelven los valores EFECTIVOS (los que va a usar el mailer) y de
        // dónde sale cada uno, para que el panel pueda decir si manda lo
        // configurado en la BD o el valor por defecto del servidor.
        return [
            'email'        => $recipient->email,
            'name'         => $recipient->name,
            'email_source' => $recipient->emailFromSettings ? 'db' : 'env',
            'name_source'  => $recipient->nameFromSettings ? 'db' : 'env',
        ];
    }
}
