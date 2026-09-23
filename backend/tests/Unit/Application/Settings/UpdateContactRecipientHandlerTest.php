<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Settings;

use App\Application\Settings\UpdateContactRecipient\UpdateContactRecipientCommand;
use App\Application\Settings\UpdateContactRecipient\UpdateContactRecipientHandler;
use App\Domain\Settings\SettingKey;
use App\Tests\TestDoubles\InMemorySettingsRepository;
use PHPUnit\Framework\TestCase;

final class UpdateContactRecipientHandlerTest extends TestCase
{
    public function testStoresBothValues(): void
    {
        $settings = new InMemorySettingsRepository();

        $this->handle($settings, 'panel@example.com', 'Nombre del panel');

        $stored = $settings->values();
        self::assertCount(2, $stored);
        self::assertSame('panel@example.com', $stored[SettingKey::CONTACT_RECIPIENT_EMAIL] ?? null);
        self::assertSame('Nombre del panel', $stored[SettingKey::CONTACT_RECIPIENT_NAME] ?? null);
    }

    public function testEmptyEmailGoesBackToEnvAndKeepsTheName(): void
    {
        $settings = new InMemorySettingsRepository([
            SettingKey::CONTACT_RECIPIENT_EMAIL => 'viejo@example.com',
        ]);

        $this->handle($settings, '', 'Nombre del panel');

        self::assertSame([SettingKey::CONTACT_RECIPIENT_NAME => 'Nombre del panel'], $settings->values());
    }

    public function testEmptyingBothFieldsClearsTheOverride(): void
    {
        $settings = new InMemorySettingsRepository([
            SettingKey::CONTACT_RECIPIENT_EMAIL => 'viejo@example.com',
            SettingKey::CONTACT_RECIPIENT_NAME => 'Nombre viejo',
        ]);

        $this->handle($settings, '', '');

        self::assertSame([], $settings->values());
    }

    public function testWhitespaceOnlyCountsAsEmpty(): void
    {
        $settings = new InMemorySettingsRepository([
            SettingKey::CONTACT_RECIPIENT_EMAIL => 'viejo@example.com',
        ]);

        $this->handle($settings, '   ', '  ');

        self::assertSame([], $settings->values());
    }

    private function handle(InMemorySettingsRepository $settings, string $email, string $name): void
    {
        (new UpdateContactRecipientHandler($settings))->handle(
            new UpdateContactRecipientCommand($email, $name),
        );
    }
}
