<?php declare(strict_types=1);

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

use ILIAS\Refinery\KindlyTo\Transformation\DictionaryTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\StringTransformation;
use ILIAS\Refinery\ConstraintViolationException;
use ILIAS\Tests\Refinery\TestCase;
use stdClass;

class DictionaryTransformationTest extends TestCase
{
    /**
     * @dataProvider DictionaryTransformationDataProvider
     * @param array $originVal
     * @param array $expectedVal
     */
    public function testDictionaryTransformation(array $originVal, array $expectedVal) : void
    {
        $transformation = new DictionaryTransformation(new StringTransformation());
        $transformedValue = $transformation->transform($originVal);
        $this->assertIsArray($transformedValue);
        $this->assertEquals($expectedVal, $transformedValue);
    }

    /**
     * @dataProvider TransformationFailingDataProvider
     * @param mixed $failingVal
     */
    public function testTransformationFailures($failingVal) : void
    {
        $this->expectException(ConstraintViolationException::class);
        $transformation = new DictionaryTransformation(new StringTransformation());
        $transformation->transform($failingVal);
    }

    public function TransformationFailingDataProvider() : array
    {
        return [
            'from_is_a_string' => ['hello'],
            'from_is_an_int' => [1],
            'from_is_an_float' => [3.141],
            'from_is_null' => [null],
            'from_is_a_bool' => [true],
            'from_is_a_resource' => [fopen('php://memory', 'rb')],
            'from_is_an_object' => [new stdClass()],
        ];
    }

    public function DictionaryTransformationDataProvider() : array
    {
        return [
            'first_arr' => [['hello' => 'world'], ['hello' => 'world'] ],
            'second_arr' => [['hi' => 'earth', 'goodbye' => 'world'], ['hi' => 'earth', 'goodbye' => 'world']],
            'third_arr' => [[22 => "earth", 33 => "world"], [22 => "earth", 33 => "world"]],
            'fourth_arr' => [[22.33 => "earth", 33.44 => "world"], [22 => "earth", 33 => "world"]],// This will result in a float rounding error in PHP >= 8.1
            'empty_array' => [[], []]
        ];
    }
}
