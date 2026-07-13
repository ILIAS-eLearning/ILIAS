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

use ILIAS\Data\Description\DValue;
use ILIAS\Data\Description\ValueType;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class DDValueTest extends TestCase
{
    #[DataProvider('casesProvider')]
    public function testIntRepresentation(ValueType $type, $value, $is_match): void
    {
        $desc = new DValue(
            $this->createMock(\ILIAS\Data\Text\SimpleDocumentMarkdown::class),
            $type
        );

        $res = $desc->getPrimitiveRepresentation($value);

        if ($is_match) {
            $this->assertEquals($value, $res);
        } else {
            $this->assertInstanceOf(\Closure::class, $res);
            $errors = iterator_to_array($res());
            $this->assertCount(1, $errors);
            $this->assertTrue(is_string($errors[0]));
        }
    }

    public static function casesProvider(): array
    {
        return [
            [ValueType::INT, 42, true],
            [ValueType::INT, "foo", false],
            [ValueType::FLOAT, 2.3, true],
            [ValueType::FLOAT, "foo", false],
            [ValueType::STRING, "foo", true],
            [ValueType::STRING, 2, false],
            [ValueType::DATETIME, new \DateTimeImmutable(), true],
            [ValueType::DATETIME, "foo", false],
            [ValueType::BOOL, true, true],
            [ValueType::BOOL, false, true],
            [ValueType::BOOL, "true", false],
            [ValueType::BOOL, 1, false],
            [ValueType::BOOL, null, false],
            [ValueType::NULL, null, true],
            [ValueType::NULL, false, false],
        ];
    }
}
