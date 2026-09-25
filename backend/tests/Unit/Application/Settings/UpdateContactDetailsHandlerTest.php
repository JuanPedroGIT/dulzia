<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Settings;

use App\Application\Settings\UpdateContactDetails\UpdateContactDetailsCommand;
use App\Application\Settings\UpdateContactDetails\UpdateContactDetailsHandler;
use App\Domain\Settings\SettingKey;
use App\Tests\TestDoubles\InMemorySettingsRepository;
use PHPUnit\Framework\TestCase;

final class UpdateContactDetailsHandlerTest extends TestCase
{
    public function testStoresBothValues(): void
    {
        $settings = new InMemorySettingsRepository();

        $this->handle($settings, 'hola@example.com', '+34 629 991 659');

        $stored = $settings->values();
        self::assertCount(2, $stored);
        self::assertSame('hola@example.com', $stored[SettingKey::CONTACT_EMAIL] ?? null);
        self::assertSame('+34 629 991 659', $stored[SettingKey::CONTACT_PHONE] ?? null);
    }

    public function testEmptyPhoneGoesBackToTheWebDefaultAndKeepsTheEmail(): void
    {
        $settings = new InMemorySettingsRepository([
            SettingKey::CONTACT_PHONE => '+34 600 111 222',
        ]);

        $this->handle($settings, 'hola@example.com', '');

        self::assertSame([SettingKey::CONTACT_EMAIL => 'hola@example.com'], $settings->values());
    }

    public function testEmptyingBothFieldsClearsTheOverride(): void
    {
        $settings = new InMemorySettingsRepository([
            SettingKey::CONTACT_EMAIL => 'viejo@example.com',
            SettingKey::CONTACT_PHONE => '+34 600 111 222',
        ]);

        $this->handle($settings, '', '');

        self::assertSame([], $settings->values());
    }

    public function testWhitespaceOnlyCountsAsEmpty(): void
    {
        $settings = new InMemorySettingsRepository([
            SettingKey::CONTACT_EMAIL => 'viejo@example.com',
            SettingKey::CONTACT_PHONE => '+34 600 111 222',
        ]);

        $this->handle($settings, '   ', '  ');

        self::assertSame([], $settings->values());
    }

    private function handle(InMemorySettingsRepository $settings, string $email, string $phone): void
    {
        (new UpdateContactDetailsHandler($settings))->handle(
            new UpdateContactDetailsCommand($email, $phone),
        );
    }
}
