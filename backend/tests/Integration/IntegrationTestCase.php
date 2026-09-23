<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Entity\AdminUser;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Base para tests HTTP de integración.
 *
 * - Usa la BD dedicada de test (dulzia_test, ver config/packages/test/doctrine.yaml).
 * - Crea el schema desde los mappings de Doctrine una vez por proceso y
 *   trunca todas las tablas antes de cada test.
 *
 * Requisito: crear la BD una vez con `make test-setup`.
 *
 * Reglas de uso en las subclases:
 * - Obtener el cliente SIEMPRE con $this->client(). No usar static::createClient():
 *   WebTestCase solo permite arrancar el kernel una vez y por esa vía.
 * - El EntityManager con $this->em() (después de haber pedido el cliente).
 */
abstract class IntegrationTestCase extends WebTestCase
{
    private const TABLES = ['admin_token', 'admin_user', 'contact_submission', 'service_example', 'service', 'settings'];

    private static bool $schemaCreated = false;
    private ?KernelBrowser $client = null;

    protected static function createKernel(array $options = []): KernelInterface
    {
        // KernelTestCase lee $_ENV['APP_ENV'] antes que $_SERVER: en el contenedor
        // vale 'dev' y pisaría el 'test' de phpunit.dist.xml. Forzamos el entorno
        // de test para que se cargue config/packages/test/*.yaml.
        return parent::createKernel(['environment' => 'test', 'debug' => true] + $options);
    }

    protected function setUp(): void
    {
        parent::setUp();
        self::ensureKernelShutdown();
        // Sin tablas no hay nada que truncar: el schema se crea en client()
        // (la primera petición), dentro del flujo de WebTestCase.
        self::resetDatabase();
    }

    protected function client(): KernelBrowser
    {
        if ($this->client === null) {
            $this->client = static::createClient();
            // El kernel ya está arrancado por createClient: aquí sí se puede
            // tocar el contenedor sin violar la regla de WebTestCase.
            self::ensureSchema();
        }

        return $this->client;
    }

    protected function em(): EntityManagerInterface
    {
        $this->client(); // garantiza un único arranque del kernel

        // El alias EntityManagerInterface se inlina al compilar el contenedor
        // de test; el registry 'doctrine' es público y siempre está disponible.
        $em = static::getContainer()->get('doctrine')->getManager();
        \assert($em instanceof EntityManagerInterface);

        return $em;
    }

    /** Conexión cruda a la BD de test, sin kernel (para el TRUNCATE). */
    private static function dbal(): Connection
    {
        return DriverManager::getConnection([
            'driver' => 'pdo_pgsql',
            'host' => 'shared-postgres-db',
            'port' => 5432,
            'dbname' => 'dulzia_test',
            'user' => 'dulzia',
            'password' => getenv('DULZIA_DB_PASS') ?: '',
        ]);
    }

    private static function ensureSchema(): void
    {
        if (self::$schemaCreated) {
            return;
        }

        $em = static::getContainer()->get('doctrine')->getManager();
        \assert($em instanceof EntityManagerInterface);

        $schemaTool = new SchemaTool($em);
        $metadata = $em->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        self::$schemaCreated = true;
    }

    private static function resetDatabase(): void
    {
        if (!self::$schemaCreated) {
            return;
        }

        $connection = self::dbal();
        foreach (self::TABLES as $table) {
            $connection->executeStatement(
                sprintf('TRUNCATE "%s" RESTART IDENTITY CASCADE', $table),
            );
        }
    }

    protected function persist(object ...$objects): void
    {
        foreach ($objects as $object) {
            $this->em()->persist($object);
        }
        $this->em()->flush();
        // Fuera identidad: el request siguiente debe hidratar de la BD
        // (p. ej. las colecciones inversas como Service::examples)
        $this->em()->clear();
    }

    protected function createAdminUser(string $username = 'admin', string $password = 'admin'): void
    {
        $this->persist(new AdminUser($username, password_hash($password, PASSWORD_BCRYPT)));
    }

    /** Hace login real contra /api/admin/login y devuelve el token. */
    protected function login(string $username = 'admin', string $password = 'admin'): string
    {
        $client = $this->client();
        $client->request('POST', '/api/admin/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['username' => $username, 'password' => $password], JSON_THROW_ON_ERROR));

        self::assertResponseIsSuccessful();

        return (string) json_decode((string) $client->getResponse()->getContent(), true)['token'];
    }

    protected function authHeaders(): array
    {
        return ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->login()];
    }
}
