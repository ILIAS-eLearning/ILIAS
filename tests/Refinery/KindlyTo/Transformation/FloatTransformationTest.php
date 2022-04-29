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

use ILIAS\Refinery\ConstraintViolationException;
use ILIAS\Refinery\KindlyTo\Transformation\FloatTransformation;
use ILIAS\Tests\Refinery\TestCase;

class FloatTransformationTest extends TestCase
{
    private FloatTransformation $transformation;

    protected function setUp() : void
    {
        $this->transformation = new FloatTransformation();
    }

    /**
     * @dataProvider FloatTestDataProvider
     * @param mixed $originVal
     * @param float $expectedVal
     */
    public function testFloatTransformation($originVal, float $expectedVal) : void
    {
        $transformedValue = $this->transformation->transform($originVal);
        $this->assertIsFloat($transformedValue);
        $this->assertEquals($expectedVal, $transformedValue);
    }

    /**
     * @dataProvider FailingTransformationDataProvider
     * @param mixed $failingVal
     */
    public function testFailingTransformations($failingVal) : void
    {
        $this->expectNotToPerformAssertions();
        try {
            $transformedValue = $this->transformation->transform($failingVal);
        } catch (ConstraintViolationException $exception) {
            return;
        }
        $this->fail();
    }

    public function FailingTransformationDataProvider() : array
    {
        return [
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
            'octal_notation1' => ["01"],
            'octal_notation2' => ["-01"],
            'mill_delim' => ["1'000"],
            'leading_dot' => [".5"],
            'leading_comma' => [",661"]
        ];
    }

    public function FloatTestDataProvider() : array
    {
        return [
            'some_float' => [1.0, 1.0],
            'pos_bool' => [true, 1.0],
            'neg_bool' => [false, 0.0],
            'string_comma' => ['234,23', 234.23],
            'neg_string_comma' => ['-234,23', -234.23],
            'neg_string_comma_trimming' => [' -234,23 ', -234.23],
            'string_point' => ['234.23', 234.23],
            'neg_string_point' => ['-234.23', -234.23],
            'neg_string_point_trimming' => [' -234.23 ', -234.23],
            'string_e_notation' => ['7E10', 70000000000],
            'string_e_notation_trimming' => [' 7E10 ', 70000000000],
            'neg_string_e_notation' => ['-7E10', -70000000000],
            'neg_string_e_notation_trimming' => [' -7E10 ', -70000000000],
            'int_val' => [23, 23.0],
            'neg_int_val' => [-2, -2.0],
            'zero_int' => [0, 0.0],
            'zero_string' => ["0", 0.0],
            'float_st_one' => [0.1, 0.1],
            'floatstr_st_one' => ['0.1', 0.1],
            'floatstr_st_one_negative' => ['-0.1', -0.1],
            'floatstr_st_one_comma' => ['0,1', 0.1],
            'floatstr_st_one_comma_negative' => ['-0,1', -0.1]
        ];
    }
}
