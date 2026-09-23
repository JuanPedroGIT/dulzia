<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Settings;

use App\Domain\Settings\ContactRecipientResolver;
use App\Domain\Settings\SettingKey;
use App\Domain\Settings\SettingsRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class ContactRecipientResolverTest extends TestCase
{
    private const DEFAULT_EMAIL = 'env@example.com';
    private const DEFAULT_NAME = 'Nombre del .env';

    /** @param array<string, string> $stored */
    private function settings(array $stored): SettingsRepositoryInterface
    {
        $settings = $this->createMock(SettingsRepositoryInterface::class);
        $settings->method('get')->willReturnCallback(
            static fn (string $key): ?string => $stored[$key] ?? null,
        );

        return $settings;
    }

    private function resolver(SettingsRepositoryInterface $settings, ?LoggerInterface $logger = null): ContactRecipientResolver
    {
        if ($logger === null) {
            $logger = $this->createMock(LoggerInterface::class);
            $logger->expects(self::never())->method('error');
        }

        return new ContactRecipientResolver(
            settings: $settings,
            logger: $logger,
            defaultEmail: self::DEFAULT_EMAIL,
            defaultName: self::DEFAULT_NAME,
        );
    }

    private function logger(): LoggerInterface
    {
        return $this->createMock(LoggerInterface::class);
    }

    public function testConfiguredValuesWinOverEnv(): void
    {
        $recipient = $this->resolver($this->settings([
            SettingKey::CONTACT_RECIPIENT_EMAIL => 'panel@example.com',
            SettingKey::CONTACT_RECIPIENT_NAME => 'Nombre del panel',
        ]))->resolve();

        self::assertSame('panel@example.com', $recipient->email);
        self::assertSame('Nombre del panel', $recipient->name);
        self::assertTrue($recipient->emailFromSettings);
        self::assertTrue($recipient->nameFromSettings);
    }

    public function testFallsBackToEnvWhenNothingIsConfigured(): void
    {
        $recipient = $this->resolver($this->settings([]))->resolve();

        self::assertSame(self::DEFAULT_EMAIL, $recipient->email);
        self::assertSame(self::DEFAULT_NAME, $recipient->name);
        self::assertFalse($recipient->emailFromSettings);
        self::assertFalse($recipient->nameFromSettings);
    }

    public function testEachFieldFallsBackIndependently(): void
    {
        $recipient = $this->resolver($this->settings([
            SettingKey::CONTACT_RECIPIENT_EMAIL => 'panel@example.com',
        ]))->resolve();

        self::assertSame('panel@example.com', $recipient->email);
        self::assertSame(self::DEFAULT_NAME, $recipient->name);
        self::assertTrue($recipient->emailFromSettings);
        self::assertFalse($recipient->nameFromSettings, 'El nombre no configurado debe caer al .env');
    }

    public function testRepositoryFailureFallsBackToEnvAndLogs(): void
    {
        $settings = $this->createMock(SettingsRepositoryInterface::class);
        $settings->method('get')->willThrowException(new \RuntimeException('relation "settings" does not exist'));

        $logger = $this->logger();
        $logger->expects(self::once())->method('error');

        $recipient = $this->resolver($settings, $logger)->resolve();

        self::assertSame(self::DEFAULT_EMAIL, $recipient->email);
        self::assertSame(self::DEFAULT_NAME, $recipient->name);
        self::assertFalse($recipient->emailFromSettings);
        self::assertFalse($recipient->nameFromSettings);
    }

    public function testSettingsAreReadOnEveryResolve(): void
    {
        // 2 claves × 2 resoluciones: si cacheara, el panel no se aplicaría
        // hasta reiniciar el contenedor.
        $settings = $this->createMock(SettingsRepositoryInterface::class);
        $settings->expects(self::exactly(4))->method('get')->willReturn(null);

        $resolver = $this->resolver($settings);
        $resolver->resolve();
        $resolver->resolve();
    }
}
