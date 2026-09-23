<?php

declare(strict_types=1);

namespace App\Tests\TestDoubles;

use App\Domain\Settings\SettingsRepositoryInterface;

/**
 * Ajustes en memoria para los tests unitarios. Respeta la misma semántica que
 * el repositorio real: un valor vacío se lee como null.
 */
final class InMemorySettingsRepository implements SettingsRepositoryInterface
{
    /** @param array<string, string> $values */
    public function __construct(
        private array $values = [],
    ) {}

    public function get(string $key): ?string
    {
        $value = $this->values[$key] ?? null;

        return ($value === null || trim($value) === '') ? null : $value;
    }

    public function set(string $key, string $value): void
    {
        $this->values[$key] = $value;
    }

    public function delete(string $key): void
    {
        unset($this->values[$key]);
    }

    /** @return array<string, string> */
    public function values(): array
    {
        return $this->values;
    }
}
