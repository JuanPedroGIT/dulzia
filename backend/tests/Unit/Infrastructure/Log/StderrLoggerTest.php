<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Log;

use App\Infrastructure\Log\StderrLogger;
use PHPUnit\Framework\TestCase;

final class StderrLoggerTest extends TestCase
{
    private string $tmpFile;

    protected function setUp(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'dulzia_log_');
        self::assertNotFalse($tmp);
        $this->tmpFile = $tmp;
    }

    protected function tearDown(): void
    {
        if (is_file($this->tmpFile)) {
            unlink($this->tmpFile);
        }
    }

    public function testWritesJsonLineWithLevelMessageAndContext(): void
    {
        $logger = new StderrLogger($this->tmpFile);

        $logger->error('Fallo al enviar email', ['submission_id' => 'abc']);

        $line = (string) file_get_contents($this->tmpFile);
        self::assertJson($line);

        $entry = json_decode($line, true);
        self::assertSame('error', $entry['level']);
        self::assertSame('Fallo al enviar email', $entry['message']);
        self::assertSame('abc', $entry['context']['submission_id']);
    }

    public function testExceptionInContextIsSerializedAsArray(): void
    {
        $logger = new StderrLogger($this->tmpFile);

        $logger->error('fallo', ['exception' => new \RuntimeException('Brevo caído')]);

        $entry = json_decode((string) file_get_contents($this->tmpFile), true);
        self::assertSame('RuntimeException', $entry['context']['exception']['class']);
        self::assertStringContainsString('Brevo caído', $entry['context']['exception']['message']);
    }

    public function testInvalidTargetThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        new StderrLogger('/no/existe/directorio/log.txt');
    }
}
