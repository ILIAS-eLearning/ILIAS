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

use ILIAS\Refinery\ConstraintViolationException;
use ILIAS\Refinery\KindlyTo\Transformation\IntegerTransformation;
use ILIAS\Tests\Refinery\TestCase;

class IntegerTransformationTest extends TestCase
{
    private IntegerTransformation $transformation;

    protected function setUp(): void
    {
        $this->transformation = new IntegerTransformation();
    }

    /**
     * @dataProvider IntegerTestDataProvider
     * @param mixed $originVal
     * @param int $expectedVal
     */
    public function testIntegerTransformation($originVal, int $expectedVal): void
    {
        $transformedValue = $this->transformation->transform($originVal);
        $this->assertIsInt($transformedValue);
        $this->assertEquals($expectedVal, $transformedValue);
    }

    /**
     * @dataProvider TransformationFailureDataProvider
     * @param mixed $failingValue
     */
    public function testTransformIsInvalid($failingValue): void
    {
        $this->expectException(ConstraintViolationException::class);
        $this->transformation->transform($failingValue);
    }

    public function IntegerTestDataProvider(): array
    {
        return [
            'pos_bool' => [true, 1],
            'neg_bool' => [false, 0],
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

    public function TransformationFailureDataProvider(): array
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
