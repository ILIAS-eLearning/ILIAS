<?php
/* Copyright (c) 2020 Luka K. A. Stocker, Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Refinery\KindlyTo\Transformation;

use ILIAS\Refinery\KindlyTo\Transformation\DictionaryTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\StringTransformation;
use ILIAS\Refinery\ConstraintViolationException;
use ILIAS\Tests\Refinery\TestCase;

class DictionaryTransformationTest extends TestCase
{
    /**
     * @dataProvider DictionaryTransformationDataProvider
     * @param $originVal
     * @param $expectedVal
     */
    public function testDictionaryTransformation($originVal, $expectedVal)
    {
        $transformation = new DictionaryTransformation(new StringTransformation());
        $transformedValue = $transformation->transform($originVal);
        $this->assertIsArray($transformedValue);
        $this->assertEquals($expectedVal, $transformedValue);
    }

    /**
     * @dataProvider TransformationFailingDataProvider
     * @param $failingVal
     */
    public function testTransformationFailures($failingVal)
    {
        $this->expectException(ConstraintViolationException::class);
        $transformation = new DictionaryTransformation(new StringTransformation());
        $transformation->transform($failingVal);
    }

    public function TransformationFailingDataProvider()
    {
        return [
            'key_not_a_string' => ['hello'],
            'value_not_a_string' => ['hello' => 1]
        ];
    }

    public function DictionaryTransformationDataProvider()
    {
        return [
            'first_arr' => [['hello' => 'world'], ['hello' => 'world'] ],
            'second_arr' => [['hi' => 'earth', 'goodbye' => 'world'], ['hi' => 'earth', 'goodbye' => 'world']],
            'third_arr' => [[22 => "earth", 33 => "world"], [22 => "earth", 33 => "world"]],
            'fourth_arr' => [[22.33 => "earth", 33.44 => "world"], [22 => "earth", 33 => "world"]],
            'empty_array' => [[], []]
        ];
    }
}
