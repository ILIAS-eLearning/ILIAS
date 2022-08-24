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

use ILIAS\Refinery\KindlyTo\Transformation\BooleanTransformation;
use ILIAS\Tests\Refinery\TestCase;
use ILIAS\Refinery\ConstraintViolationException;

class BooleanTransformationTest extends TestCase
{
    private BooleanTransformation $transformation;

    protected function setUp(): void
    {
        $this->transformation = new BooleanTransformation();
    }

    /**
     * @dataProvider BooleanTestDataProvider
     * @param mixed $originVal
     * @param bool $expectedVal
     */
    public function testBooleanTransformation($originVal, bool $expectedVal): void
    {
        $transformedValue = $this->transformation->transform($originVal);
        $this->assertIsBool($transformedValue);
        $this->assertSame($expectedVal, $transformedValue);
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

    public function BooleanTestDataProvider(): array
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

    public function TransformationFailureDataProvider(): array
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
