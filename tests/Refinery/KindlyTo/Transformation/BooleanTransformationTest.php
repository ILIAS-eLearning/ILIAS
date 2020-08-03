<?php
/* Copyright (c) 2020 Luka K. A. Stocker, Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Refinery\KindlyTo\Transformation;

use ILIAS\Refinery\KindlyTo\Transformation\BooleanTransformation;
use ILIAS\Tests\Refinery\TestCase;
use ILIAS\Refinery\ConstraintViolationException;

/**
 * Test transformations in this Group
 */
class BooleanTransformationTest extends TestCase
{
    private $transformation;

    public function setUp() : void
    {
        $this->transformation = new BooleanTransformation();
    }

    /**
     * @dataProvider BooleanTestDataProvider
     * @param $originVal
     * @param bool $expectedVal
     */
    public function testBooleanTransformation($originVal, $expectedVal)
    {
        $transformedValue = $this->transformation->transform($originVal);
        $this->assertIsBool($transformedValue);
        $this->assertSame($expectedVal, $transformedValue);
    }

    /**
     * @dataProvider TransformationFailureDataProvider
     * @param $failingValue
     */
    public function testTransformIsInvalid($failingValue)
    {
        $this->expectException(ConstraintViolationException::class);
        $this->transformation->transform($failingValue);
    }

    public function BooleanTestDataProvider()
    {
        return [
            'true' => [true, true],
            'false' => [false, false],
            'pos_boolean1' => ['true', true],
            'pos_boolean2' => ['TRUE', true],
            'pos_boolean3' => ['True', true],
            'pos_boolean4' => ['tRuE', true],
            'pos_boolean_number' => [1, true],
            'pos_boolean_number_string' => ['1', true],
            'neg_boolean1' => ['false', false],
            'neg_boolean2' => ['FALSE', false],
            'neg_boolean3' => ['False', false],
            'neg_boolean4' => ['fAlSe', false],
            'neg_boolean_number' => [0, false],
            'neg_boolean_number_string' => ['0', false]
        ];
    }


    public function TransformationFailureDataProvider()
    {
        return [
            'null' => [null],
            'null_as_string' => ["null"],
            'float_zero' => [0.0],
            'float_one' => [1.0],
            'two' => [2],
            'two_as_string' => ["2"],
            'some_array' => [[]],
            'some_string' => [""]
        ];
    }
}
