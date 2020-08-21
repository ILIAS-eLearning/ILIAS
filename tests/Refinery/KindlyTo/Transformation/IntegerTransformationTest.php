<?php
/* Copyright (c) 2020 Luka K. A. Stocker, Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Refinery\KindlyTo\Transformation;

use ILIAS\Refinery\ConstraintViolationException;
use ILIAS\Refinery\KindlyTo\Transformation\IntegerTransformation;
use ILIAS\Tests\Refinery\TestCase;

/**
 * Test transformations in this Group
 */
class IntegerTransformationTest extends TestCase
{
    private $transformation;

    public function setUp() : void
    {
        $this->transformation = new IntegerTransformation();
    }

    /**
     * @dataProvider IntegerTestDataProvider
     * @param $originVal
     * @param $expectedVal
     */
    public function testIntegerTransformation($originVal, $expectedVal)
    {
        $transformedValue = $this->transformation->transform($originVal);
        $this->assertIsInt($transformedValue);
        $this->assertEquals($expectedVal, $transformedValue);
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

    public function IntegerTestDataProvider()
    {
        return [
            'pos_bool' => [true, (int) 1],
            'neg_bool' => [false, (int) 0],
            'float_val' => [20.5, 21],
            'float_val_round_up' => [0.51, 1],
            'float_val_round_down' => [0.49, 0],
            'string_zero' => ['0', 0],
            'string_val' => ['4947642', 4947642],
            'neg_string_val' => ['-4947642', -4947642],
            'string_val_trimming' => [' 4947642 ', 4947642],
            'neg_string_val_trimming' => [' -4947642 ', -4947642],
        ];
    }

    public function TransformationFailureDataProvider()
    {
        return [
            'bigger_than_int_max' => ["9223372036854775808"],
            'smaller_than_int_min' => ["-9223372036854775809"],
            'weird_notation' => ["01"],
            'some_array' => [[]],
            'mill_delim' => ["1'000"],
            'null' => [null],
            'empty' => [""],
            'written_false' => ['false'],
            'written_null' => ['null'],
            'NaN' => [NAN],
            'written_NaN' => ['NaN'],
            'INF' => [INF],
            'neg_INF' => [-INF],
            'written_INF' => ['INF'],
            'written_neg_INF' => ['-INF'],
        ];
    }
}
