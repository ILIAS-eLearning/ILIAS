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

/**
 * @author  Richard Klees <richard.klees@concepts-and-training.de>
 */

namespace ILIAS\Data;

use ILIAS\Data\Description\Description;
use ILIAS\Data\Description\DValue;
use ILIAS\Data\Description\DMap;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class DMapTest extends TestCase
{
    protected DMap $m;
    protected DValue $k;
    protected Description $v;

    public function setUp(): void
    {
        $this->k = $this->createMock(DValue::class);
        $this->v = $this->createMock(Description::class);
        $this->m = new DMap(
            $this->createMock(\ILIAS\Data\Text\SimpleDocumentMarkdown::class),
            $this->k,
            $this->v
        );
    }

    #[DataProvider('obviousNoMatchProvider')]
    public function testObviouslyNotMatching($data): void
    {
        $res = $this->m->getPrimitiveRepresentation($data);

        $this->assertInstanceOf(\Closure::class, $res);
        $errors = iterator_to_array($res());
        $this->assertCount(1, $errors);
        $this->assertTrue(is_string($errors[0]));
    }

    public static function obviousNoMatchProvider(): array
    {
        return [
            [1], ["1"], [null], [true], [new \StdClass()], [new \DateTimeImmutable()]
        ];
    }

    public function testEmptyMatches(): void
    {
        $res = $this->m->getPrimitiveRepresentation([]);

        $this->assertEquals([], $res);
    }

    public function testForwardsToSubDescriptions(): void
    {
        $data = [
            "a" => 1,
            "b" => 2
        ];

        $keys = ["c", "d"];
        $this->k->expects($this->exactly(2))
            ->method("getPrimitiveRepresentation")
            ->willReturnCallback(function ($v) use (&$keys) {
                array_push($keys, $v);
                return array_shift($keys);
            });

        $values = [3, 4];
        $this->v->expects($this->exactly(2))
            ->method("getPrimitiveRepresentation")
            ->willReturnCallback(function ($v) use (&$values) {
                array_push($values, $v);
                return array_shift($values);
            });

        $expected = [
            "c" => 3,
            "d" => 4
        ];

        $res = $this->m->getPrimitiveRepresentation($data);

        $this->assertEquals($expected, $res);
        $this->assertEquals(["a", "b"], $keys);
        $this->assertEquals([1, 2], $values);
    }

    public function testFailsOnKeyFailure(): void
    {
        $data = ["a" => 1];

        $this->v
            ->method("getPrimitiveRepresentation")
            ->willReturn(1);

        $this->k
            ->method("getPrimitiveRepresentation")
            ->willReturn(fn() => yield "FAILURE");

        $res = $this->m->getPrimitiveRepresentation($data);

        $this->assertInstanceOf(\Closure::class, $res);
        $errors = iterator_to_array($res());
        $this->assertCount(1, $errors);
        $this->assertTrue(is_string($errors[0]));
        $this->assertTrue(str_contains($errors[0], "FAILURE"));
    }

    public function testFailsOnValueFailure(): void
    {
        $data = ["a" => 1];

        $this->v
            ->method("getPrimitiveRepresentation")
            ->willReturn(fn() => yield "FAILURE");

        $this->k
            ->method("getPrimitiveRepresentation")
            ->willReturn("a");

        $res = $this->m->getPrimitiveRepresentation($data);

        $this->assertInstanceOf(\Closure::class, $res);
        $errors = iterator_to_array($res());
        $this->assertCount(1, $errors);
        $this->assertTrue(is_string($errors[0]));
        $this->assertTrue(str_contains($errors[0], "FAILURE"));
    }
}
