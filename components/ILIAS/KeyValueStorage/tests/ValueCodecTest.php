<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Tests\KeyValueStorage;

use ILIAS\KeyValueStorage\Exception\InvalidStoragePayloadException;
use ILIAS\KeyValueStorage\ValueCodec;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ValueCodecTest extends TestCase
{
    private ValueCodec $codec;

    protected function setUp(): void
    {
        $this->codec = new ValueCodec();
    }

    #[DataProvider('roundTripValueProvider')]
    public function testRoundTripsValue(mixed $value): void
    {
        self::assertSame($value, $this->codec->decode($this->codec->encode($value)));
    }

    /**
     * @return array<string, array{0: mixed}>
     */
    public static function roundTripValueProvider(): array
    {
        return [
            'string' => ['title'],
            'integer' => [42],
            'float' => [3.14],
            'boolean true' => [true],
            'boolean false' => [false],
            'null' => [null],
            'array' => [['sort' => 'title', 'direction' => 'asc', 'count' => 3, 'enabled' => true]],
            'nested array' => [['filters' => ['status' => 'open', 'limit' => 10], 'page' => 1]],
            'list array' => [[1, 2, 3]],
        ];
    }

    public function testEncodeProducesPlainJsonForScalar(): void
    {
        self::assertSame('"title"', $this->codec->encode('title'));
    }

    public function testEncodeProducesPlainJsonForArray(): void
    {
        self::assertSame(
            '{"sort":"title","count":3}',
            $this->codec->encode(['sort' => 'title', 'count' => 3])
        );
    }

    public function testEncodesJsonSerializableObjectViaJsonEncode(): void
    {
        $value = new JsonSerializableStorageValue(['sort' => 'title']);

        self::assertSame('{"sort":"title"}', $this->codec->encode($value));
        self::assertSame(['sort' => 'title'], $this->codec->decode($this->codec->encode($value)));
    }

    public function testEncodesJsonSerializableObjectInNestedArray(): void
    {
        $value = [
            'state' => new JsonSerializableStorageValue(['page' => 2]),
            'enabled' => true,
        ];

        self::assertSame(
            '{"state":{"page":2},"enabled":true}',
            $this->codec->encode($value)
        );
    }

    public function testRejectsObjectsWithoutJsonSerializableOnEncode(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Only JSON-serializable values can be stored');

        $this->codec->encode(new \stdClass());
    }

    public function testRejectsNestedObjectWithoutJsonSerializableOnEncode(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Only JSON-serializable values can be stored');

        $this->codec->encode(['item' => new \stdClass()]);
    }

    public function testRejectsResourceOnEncode(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Only JSON-serializable values can be stored');

        $this->codec->encode(\fopen('php://memory', 'rb'));
    }

    public function testRejectsJsonSerializableThatReturnsObjectOnEncode(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Only JSON-serializable values can be stored');

        $this->codec->encode(new JsonSerializableReturningObject());
    }

    public function testThrowsInvalidStoragePayloadExceptionForMalformedJson(): void
    {
        $this->expectException(InvalidStoragePayloadException::class);
        $this->expectExceptionMessage('Stored value is not valid JSON.');

        $this->codec->decode('{invalid');
    }

    public function testWrapsJsonExceptionAsPrevious(): void
    {
        try {
            $this->codec->decode('{invalid');
            self::fail('Expected InvalidStoragePayloadException.');
        } catch (InvalidStoragePayloadException $exception) {
            self::assertSame('Stored value is not valid JSON.', $exception->getMessage());
            self::assertInstanceOf(\JsonException::class, $exception->getPrevious());
        }
    }

    public function testDecodeAcceptsTopLevelScalarJson(): void
    {
        self::assertSame('title', $this->codec->decode('"title"'));
        self::assertSame(42, $this->codec->decode('42'));
        self::assertTrue($this->codec->decode('true'));
        self::assertNull($this->codec->decode('null'));
    }
}

final readonly class JsonSerializableStorageValue implements \JsonSerializable
{
    /** @param array<string, mixed> $data */
    public function __construct(private array $data)
    {
    }

    public function jsonSerialize(): array
    {
        return $this->data;
    }
}

final readonly class JsonSerializableReturningObject implements \JsonSerializable
{
    public function jsonSerialize(): \stdClass
    {
        return new \stdClass();
    }
}
