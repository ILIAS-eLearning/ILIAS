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

namespace ILIAS\Tests\KeyValueStorage\Internal;

use ILIAS\KeyValueStorage\Exception\InvalidStoredValueException;
use ILIAS\KeyValueStorage\Internal\Values;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ValuesTest extends TestCase
{
    private Values $values;

    protected function setUp(): void
    {
        $this->values = new Values();
    }

    #[DataProvider('roundTrippableValues')]
    public function testValuesSurviveARoundTrip(mixed $value): void
    {
        $this->assertSame($value, $this->values->decode($this->values->encode($value)));
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function roundTrippableValues(): array
    {
        return [
            'null' => [null],
            'true' => [true],
            'false' => [false],
            'int' => [42],
            'float' => [1.5],
            'string' => ['title'],
            'empty string' => [''],
            'list' => [['a', 'b']],
            'map' => [['sort' => 'title', 'direction' => 'asc']],
            'nested' => [['filter' => ['status' => ['open', 'closed'], 'limit' => 10]]],
            'empty array' => [[]],
        ];
    }

    public function testJsonSerializableIsStoredAsItsJsonForm(): void
    {
        $value = new class () implements \JsonSerializable {
            /**
             * @return array{a: int}
             */
            public function jsonSerialize(): array
            {
                return ['a' => 1];
            }
        };

        $this->assertSame('{"a":1}', $this->values->encode($value));
        $this->assertSame(['a' => 1], $this->values->decode($this->values->encode($value)));
    }

    public function testAJsonSerializableMayReturnAScalar(): void
    {
        $value = new class () implements \JsonSerializable {
            public function jsonSerialize(): string
            {
                return 'title';
            }
        };

        $this->assertSame('title', $this->values->decode($this->values->encode($value)));
    }

    public function testAJsonSerializableInsideAnArrayIsAccepted(): void
    {
        $inner = new class () implements \JsonSerializable {
            /**
             * @return array{a: int}
             */
            public function jsonSerialize(): array
            {
                return ['a' => 1];
            }
        };

        $this->assertSame(
            ['wrapped' => ['a' => 1]],
            $this->values->decode($this->values->encode(['wrapped' => $inner]))
        );
    }

    public function testAJsonSerializableReturningSomethingUnstorableIsRejected(): void
    {
        $value = new class () implements \JsonSerializable {
            /**
             * @return array{bad: \stdClass}
             */
            public function jsonSerialize(): array
            {
                return ['bad' => new \stdClass()];
            }
        };

        $this->expectException(\InvalidArgumentException::class);

        $this->values->encode($value);
    }

    #[DataProvider('unstorableValues')]
    public function testUnstorableValuesAreRejected(mixed $value): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->values->encode($value);
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function unstorableValues(): array
    {
        return [
            'plain object' => [new \stdClass()],
            'object in array' => [['a' => new \stdClass()]],
            'closure' => [static fn() => null],
            'not a number' => [NAN],
        ];
    }

    public function testStoredGarbageIsReported(): void
    {
        $this->expectException(InvalidStoredValueException::class);

        $this->values->decode('{not json');
    }
}
