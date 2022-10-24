<?php

declare(strict_types=1);

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

namespace ILIAS\Tests\Refinery\KindlyTo\Transformation;

use ILIAS\Refinery\KindlyTo\Transformation\StringTransformation;
use ILIAS\Tests\Refinery\TestCase;
use stdClass;

class StringTransformationTest extends TestCase
{
    private StringTransformation $transformation;

    protected function setUp(): void
    {
        $this->transformation = new StringTransformation();
    }

    /**
     * @dataProvider StringTestDataProvider
     * @param mixed $originVal
     * @param string $expectedVal
     */
    public function testStringTransformation($originVal, string $expectedVal): void
    {
        $transformedValue = $this->transformation->transform($originVal);
        $this->assertIsString($transformedValue);
        $this->assertEquals($expectedVal, $transformedValue);
    }

    public function StringTestDataProvider(): array
    {
        $obj = new class () extends stdClass {
            public function __toString()
            {
                return 'an object';
            }
        };
        return [
            'string_val' => ['hello', 'hello'],
            'int_val' => [300, '300'],
            'neg_int_val' => [-300, '-300'],
            'zero_int_val' => [0, '0'],
            'pos_bool' => [true, 'true'],
            'neg_bool' => [false, 'false'],
            'float_val' => [20.5, '20.5'],
            'object_val' => [$obj, 'an object']
        ];
    }
}
