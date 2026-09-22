<?php

namespace App\Infrastructure\Log;

use Psr\Log\AbstractLogger;

/**
 * Logger mínimo al stderr del contenedor (visible con `docker compose logs`).
 * Sin dependencia de Monolog: adaptador propio pequeño, como BrevoMailer.
 * En tests se inyecta una ruta de fichero temporal como destino.
 */
final class StderrLogger extends AbstractLogger
{
    /** @var resource */
    private $stream;

    public function __construct(string $target = 'php://stderr')
    {
        $stream = fopen($target, 'a');
        if ($stream === false) {
            throw new \RuntimeException("No se pudo abrir el stream de log: $target");
        }
        $this->stream = $stream;
    }

    public function log($level, string|\Stringable $message, array $context = []): void
    {
        // json_encode no serializa excepciones útiles: se convierte a array
        if (isset($context['exception']) && $context['exception'] instanceof \Throwable) {
            $e = $context['exception'];
            $context['exception'] = [
                'class'   => $e::class,
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ];
        }

        $line = json_encode(
            [
                'datetime' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
                'level'    => $level,
                'message'  => $message,
                'context'  => $context,
            ],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        // El logger nunca debe romper el flujo de la aplicación
        try {
            fwrite($this->stream, $line . PHP_EOL);
        } catch (\Throwable) {
        }
    }
}
