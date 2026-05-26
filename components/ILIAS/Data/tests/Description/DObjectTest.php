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
use ILIAS\Data\Description\ValueType;
use ILIAS\Data\Description\DObject;
use ILIAS\Data\Description\Field;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class DObjectTest extends TestCase
{
    #[DataProvider('simpleObjectsProvider')]
    public function testSimpleObject(string $field, $object, $expected): void
    {
        $md = $this->createMock(\ILIAS\Data\Text\SimpleDocumentMarkdown::class);
        $desc = new DObject(
            $md,
            new Field(
                $field,
                new DValue(
                    $this->createMock(\ILIAS\Data\Text\SimpleDocumentMarkdown::class),
                    ValueType::INT
                )
            )
        );

        $res = $desc->getPrimitiveRepresentation($object);

        if (!is_null($expected)) {
            $this->assertEquals($expected, $res);
        } else {
            $this->assertInstanceOf(\Closure::class, $res);
            $errors = iterator_to_array($res());
            $this->assertCount(1, $errors);
            $this->assertTrue(is_string($errors[0]));
        }
    }

    public static function simpleObjectsProvider(): array
    {
        $v1 = "value";
        $o1_a = new class () {
            public function getValue(): int
            {
                return 42;
            }
        };
        $o1_b = new class () {
            public $value = 42;
        };
        $e1 = new \StdClass();
        $e1->value = 42;

        $v2 = "some_value";
        $o2_a = new class () {
            public function getSomeValue(): int
            {
                return 23;
            }
        };
        $o2_b = new class () {
            public $some_value = 23;
        };
        $o2_c = new class () {
            public $someValue = 23;
        };
        $e2 = new \StdClass();
        $e2->some_value = 23;


        return [
            [$v1, $o1_a, $e1],
            [$v1, $o1_b, $e1],
            [$v2, $o2_a, $e2],
            [$v2, $o2_b, $e2],
            [$v2, $o2_c, $e2],
        ];
    }

    #[DataProvider('fieldNamesProvider')]
    public function testAllowedFieldNames(string $name, bool $is_allowed): void
    {
        if (!$is_allowed) {
            $this->expectException(\InvalidArgumentException::class);
        }

        $field = new Field($name, $this->createMock(Description::class));
        $this->assertEquals($name, $field->getName());
    }

    public static function fieldNamesProvider(): array
    {
        return [
            ["someName", true],
            ["some_name", true],
            ["some", true],
            ["some1", true],
            ["1some", false],
            ["some one", false]
        ];
    }

    #[DataProvider('obviousNoMatchProvider')]
    public function testObviouslyNotMatching($data): void
    {
        $desc = new DObject(
            $this->createMock(\ILIAS\Data\Text\SimpleDocumentMarkdown::class)
        );
        $res = $desc->getPrimitiveRepresentation($data);

        $this->assertInstanceOf(\Closure::class, $res);
        $errors = iterator_to_array($res());
        $this->assertCount(1, $errors);
        $this->assertTrue(is_string($errors[0]));
    }

    public static function obviousNoMatchProvider(): array
    {
        return [
            [1], ["1"], [null], [true], [[]]
        ];
    }
}
