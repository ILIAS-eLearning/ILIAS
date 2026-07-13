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

namespace ILIAS\Data\Description;

use ILIAS\Data\Description\Description;
use ILIAS\Data\Description\DValue;
use ILIAS\Data\Description\DList;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class DListTest extends TestCase
{
    protected DList $l;
    protected Description $v;

    public function setUp(): void
    {
        $this->v = $this->createMock(Description::class);
        $this->l = new DList(
            $this->createMock(\ILIAS\Data\Text\SimpleDocumentMarkdown::class),
            $this->v
        );
    }

    #[DataProvider('obviousNoMatchProvider')]
    public function testObviouslyNotMatching($data): void
    {
        $res = $this->l->getPrimitiveRepresentation($data);

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
        $res = $this->l->getPrimitiveRepresentation([]);

        $this->assertEquals([], $res);
    }

    public function testForwardsToSubDescription(): void
    {
        $data = ["a","b"];

        $values = ["c", "d"];
        $this->v->expects($this->exactly(2))
            ->method("getPrimitiveRepresentation")
            ->willReturnCallback(function ($v) use (&$values) {
                array_push($values, $v);
                return array_shift($values);
            });

        $expected = ["c", "d"];

        $res = $this->l->getPrimitiveRepresentation($data);

        $this->assertEquals($expected, $res);
        $this->assertEquals(["a", "b"], $values);
    }

    public function testFailsOnValueFailure(): void
    {
        $data = ["a"];

        $this->v
            ->method("getPrimitiveRepresentation")
            ->willReturn(fn() => yield "FAILURE");

        $res = $this->l->getPrimitiveRepresentation($data);

        $this->assertInstanceOf(\Closure::class, $res);
        $errors = iterator_to_array($res());
        $this->assertCount(1, $errors);
        $this->assertTrue(is_string($errors[0]));
        $this->assertTrue(str_contains($errors[0], "FAILURE"));
    }

    public function testRenumbersEntrys(): void
    {
        $data = [2 => "a", 27 => "b"];

        $this->v
            ->method("getPrimitiveRepresentation")
            ->willReturnCallback(fn($v) => $v);

        $res = $this->l->getPrimitiveRepresentation($data);

        $expected = ["a", "b"];

        $this->assertEquals($expected, $res);
        $this->assertNotEquals($data, $res);
    }
}
