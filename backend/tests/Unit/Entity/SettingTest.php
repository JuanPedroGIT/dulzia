<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Setting;
use PHPUnit\Framework\TestCase;

final class SettingTest extends TestCase
{
    public function testConstructorStoresKeyAndValue(): void
    {
        $setting = new Setting('contact_recipient_email', 'hola@example.com');

        self::assertSame('contact_recipient_email', $setting->getKey());
        self::assertSame('hola@example.com', $setting->getValue());
    }

    public function testSetValueChangesValueAndRefreshesTimestamp(): void
    {
        $setting = new Setting('contact_recipient_email', 'viejo@example.com');
        $antes = $setting->getUpdatedAt();

        $setting->setValue('nuevo@example.com');

        self::assertSame('nuevo@example.com', $setting->getValue());
        self::assertSame('contact_recipient_email', $setting->getKey(), 'La clave no debe cambiar');
        self::assertGreaterThanOrEqual($antes, $setting->getUpdatedAt());
    }
}
