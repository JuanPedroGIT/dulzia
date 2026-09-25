<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Settings;

use App\Domain\Settings\ContactDetailsResolver;
use App\Domain\Settings\SettingKey;
use App\Domain\Settings\SettingsRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class ContactDetailsResolverTest extends TestCase
{
    /** @param array<string, string> $stored */
    private function settings(array $stored): SettingsRepositoryInterface
    {
        $settings = $this->createMock(SettingsRepositoryInterface::class);
        $settings->method('get')->willReturnCallback(
            static fn (string $key): ?string => $stored[$key] ?? null,
        );

        return $settings;
    }

    private function resolver(SettingsRepositoryInterface $settings, ?LoggerInterface $logger = null): ContactDetailsResolver
    {
        if ($logger === null) {
            $logger = $this->createMock(LoggerInterface::class);
            $logger->expects(self::never())->method('error');
        }

        return new ContactDetailsResolver(settings: $settings, logger: $logger);
    }

    public function testConfiguredValuesWin(): void
    {
        $details = $this->resolver($this->settings([
            SettingKey::CONTACT_EMAIL => 'hola@example.com',
            SettingKey::CONTACT_PHONE => '+34 600 111 222',
        ]))->resolve();

        self::assertSame('hola@example.com', $details->email);
        self::assertSame('+34 600 111 222', $details->phone);
        self::assertTrue($details->emailFromSettings);
        self::assertTrue($details->phoneFromSettings);
    }

    public function testNothingConfiguredResolvesToNull(): void
    {
        // A diferencia del destinatario del mailer, aquí no hay valor por
        // defecto en el servidor: el que se pinta lo pone la web.
        $details = $this->resolver($this->settings([]))->resolve();

        self::assertNull($details->email);
        self::assertNull($details->phone);
        self::assertFalse($details->emailFromSettings);
        self::assertFalse($details->phoneFromSettings);
    }

    public function testEachFieldIsConfiguredIndependently(): void
    {
        $details = $this->resolver($this->settings([
            SettingKey::CONTACT_PHONE => '+34 600 111 222',
        ]))->resolve();

        self::assertNull($details->email);
        self::assertSame('+34 600 111 222', $details->phone);
        self::assertFalse($details->emailFromSettings);
        self::assertTrue($details->phoneFromSettings);
    }

    public function testRepositoryFailureResolvesToNullAndLogs(): void
    {
        $settings = $this->createMock(SettingsRepositoryInterface::class);
        $settings->method('get')->willThrowException(new \RuntimeException('relation "settings" does not exist'));

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $details = $this->resolver($settings, $logger)->resolve();

        self::assertNull($details->email);
        self::assertNull($details->phone);
    }

    public function testSettingsAreReadOnEveryResolve(): void
    {
        // 2 claves × 2 resoluciones: si cacheara, un cambio en el panel no se
        // vería hasta reiniciar el contenedor.
        $settings = $this->createMock(SettingsRepositoryInterface::class);
        $settings->expects(self::exactly(4))->method('get')->willReturn(null);

        $resolver = $this->resolver($settings);
        $resolver->resolve();
        $resolver->resolve();
    }
}
