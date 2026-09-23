<?php

namespace App\Infrastructure\Repository;

use App\Domain\Settings\SettingsRepositoryInterface;
use App\Entity\Setting;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineSettingsRepository implements SettingsRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    public function get(string $key): ?string
    {
        $setting = $this->em->find(Setting::class, $key);

        if ($setting === null) {
            return null;
        }

        $value = $setting->getValue();

        return trim($value) === '' ? null : $value;
    }

    public function set(string $key, string $value): void
    {
        // UPSERT atómico en vez de find-then-persist: quita de en medio la
        // carrera entre dos escrituras simultáneas (que daría un
        // UniqueConstraintViolationException convertido en 500 en el panel).
        $this->em->getConnection()->executeStatement(
            'INSERT INTO settings (key, value, updated_at)
             VALUES (:key, :value, :updatedAt)
             ON CONFLICT (key) DO UPDATE SET value = EXCLUDED.value, updated_at = EXCLUDED.updated_at',
            [
                'key' => $key,
                'value' => $value,
                // Se genera en PHP (no NOW()) para que no discrepe de la zona
                // horaria con la que Doctrine escribe el resto de timestamps.
                'updatedAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            ],
        );
    }

    public function delete(string $key): void
    {
        $this->em->getConnection()->executeStatement(
            'DELETE FROM settings WHERE key = :key',
            ['key' => $key],
        );
    }
}
