<?php

namespace App\Application\Settings\GetContactDetails;

use App\Domain\Settings\ContactDetailsResolver;

final class GetContactDetailsHandler
{
    public function __construct(
        private ContactDetailsResolver $details,
    ) {}

    public function handle(GetContactDetailsQuery $query): array
    {
        $details = $this->details->resolve();

        // `null` significa "sin configurar": el panel lo enseña como campo vacío
        // y la web cae a su valor por defecto. Se acompaña de dónde sale cada
        // campo para que el panel pueda decirlo.
        return [
            'email'        => $details->email,
            'phone'        => $details->phone,
            'email_source' => $details->emailFromSettings ? 'db' : 'default',
            'phone_source' => $details->phoneFromSettings ? 'db' : 'default',
        ];
    }
}
