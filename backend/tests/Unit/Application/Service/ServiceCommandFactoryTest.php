<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service;

use App\Application\Service\ServiceCommandFactory;
use App\Domain\Shared\InvalidInputException;
use App\Tests\Support\TestFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class ServiceCommandFactoryTest extends TestCase
{
    private function jsonRequest(array $body): Request
    {
        return Request::create(
            '/api/admin/services',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($body, JSON_THROW_ON_ERROR),
        );
    }

    /**
     * Petición como la que manda el panel: multipart, con `features[]` y la foto.
     */
    private function multipartRequest(array $fields, array $files = []): Request
    {
        return Request::create(
            '/api/admin/services',
            'POST',
            $fields,
            [],
            $files,
            ['CONTENT_TYPE' => 'multipart/form-data; boundary=----dulziaTest'],
        );
    }

    public function testCreatesCommandFromJsonBody(): void
    {
        $command = (new ServiceCommandFactory())->createFromRequest($this->jsonRequest([
            'name' => '  Candy Bar  ',
            'emoji' => '🍬',
            'description' => 'Descripción',
            'features' => [' Chuches ', '', 'Personalizado'],
            'category' => 'food',
        ]));

        self::assertSame('Candy Bar', $command->name);
        self::assertSame('🍬', $command->emoji);
        self::assertSame('Descripción', $command->description);
        self::assertSame(['Chuches', 'Personalizado'], $command->features);
        self::assertSame('food', $command->category);
    }

    public function testDefaultsCategoryToFood(): void
    {
        $command = (new ServiceCommandFactory())->createFromRequest($this->jsonRequest([
            'name' => 'X', 'emoji' => 'E', 'description' => 'D',
        ]));

        self::assertSame('food', $command->category);
        self::assertSame([], $command->features);
    }

    public function testBuildsUpdateCommandWithId(): void
    {
        $command = (new ServiceCommandFactory())->updateFromRequest('candy-bar', $this->jsonRequest([
            'name' => 'Candy XL', 'emoji' => '🍭', 'description' => 'D', 'features' => [], 'category' => 'food',
        ]));

        self::assertSame('candy-bar', $command->id);
        self::assertSame('Candy XL', $command->name);
    }

    public function testThrowsInvalidInputWhenRequiredFieldsMissing(): void
    {
        $this->expectException(InvalidInputException::class);
        $this->expectExceptionMessage('name y description son requeridos');

        (new ServiceCommandFactory())->createFromRequest($this->jsonRequest([
            'name' => '', 'emoji' => '🍬', 'description' => '',
        ]));
    }

    public function testCreatesCommandFromMultipartBodyWithImage(): void
    {
        $file = TestFactory::uploadedFile();

        $command = (new ServiceCommandFactory())->createFromRequest($this->multipartRequest([
            'name' => 'Candy Bar',
            'description' => 'Descripción',
            'features' => ['Chuches', 'Personalizado'],
            'category' => 'food',
        ], ['image' => $file]));

        self::assertSame('Candy Bar', $command->name);
        self::assertSame(['Chuches', 'Personalizado'], $command->features);
        self::assertSame($file, $command->image);
    }

    public function testEmojiIsOptional(): void
    {
        // El panel ya no edita el emoji: en el alta llega vacío y en la edición
        // null (conservar el que tuviera la sección).
        $factory = new ServiceCommandFactory();

        $created = $factory->createFromRequest($this->multipartRequest([
            'name' => 'Nueva', 'description' => 'D',
        ]));
        self::assertSame('', $created->emoji);

        $updated = $factory->updateFromRequest('candy-bar', $this->multipartRequest([
            'name' => 'Nueva', 'description' => 'D',
        ]));
        self::assertNull($updated->emoji);
    }

    public function testKeepsEmojiWhenItArrives(): void
    {
        $command = (new ServiceCommandFactory())->updateFromRequest('candy-bar', $this->jsonRequest([
            'name' => 'X', 'emoji' => '🍬', 'description' => 'D',
        ]));

        self::assertSame('🍬', $command->emoji);
    }

    public function testReadsRemoveImageFlag(): void
    {
        $factory = new ServiceCommandFactory();

        $multipart = $factory->updateFromRequest('candy-bar', $this->multipartRequest([
            'name' => 'X', 'description' => 'D', 'removeImage' => '1',
        ]));
        self::assertTrue($multipart->removeImage);

        $json = $factory->updateFromRequest('candy-bar', $this->jsonRequest([
            'name' => 'X', 'description' => 'D',
        ]));
        self::assertFalse($json->removeImage);
        self::assertNull($json->image);
    }

    public function testReadsThumbnailNextToTheImage(): void
    {
        $image = TestFactory::uploadedFile();
        $thumbnail = TestFactory::uploadedFile();
        $factory = new ServiceCommandFactory();

        $created = $factory->createFromRequest($this->multipartRequest(
            ['name' => 'Candy Bar', 'description' => 'D'],
            ['image' => $image, 'thumbnail' => $thumbnail],
        ));
        self::assertSame($image, $created->image);
        self::assertSame($thumbnail, $created->thumbnail);

        $updated = $factory->updateFromRequest('candy-bar', $this->multipartRequest(
            ['name' => 'Candy Bar', 'description' => 'D'],
            ['image' => $image, 'thumbnail' => $thumbnail],
        ));
        self::assertSame($thumbnail, $updated->thumbnail);
    }

    public function testThumbnailIsNullWithoutFile(): void
    {
        $command = (new ServiceCommandFactory())->createFromRequest($this->multipartRequest([
            'name' => 'Nueva', 'description' => 'D',
        ]));

        self::assertNull($command->image);
        self::assertNull($command->thumbnail);
    }
}
