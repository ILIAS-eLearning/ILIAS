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
use ILIAS\Refinery\KindlyTo\Transformation\IntegerTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\TupleTransformation;
use ILIAS\Tests\Refinery\TestCase;

class TupleTransformationTest extends TestCase
{
    private const TUPLE_KEY = 'hello';

    /**
     * @dataProvider TupleTransformationDataProvider
     * @param array $originVal
     * @param array $expectedVal
     */
    public function testTupleTransformation(array $originVal, array $expectedVal) : void
    {
        $transformation = new TupleTransformation(
            [
                new IntegerTransformation(),
                new IntegerTransformation()
            ]
        );
        $transformedValue = $transformation->transform($originVal);
        $this->assertIsArray($transformedValue);
        $this->assertEquals($expectedVal, $transformedValue);
    }

    /**
     * @dataProvider TupleFailingTransformationDataProvider
     * @param array $failingVal
     */
    public function testNewTupleIsIncorrect(array $failingVal) : void
    {
        $this->expectNotToPerformAssertions();
        $transformation = new TupleTransformation(
            [
                new IntegerTransformation(),
                self::TUPLE_KEY => new IntegerTransformation()
            ]
        );

        try {
            $result = $transformation->transform($failingVal);
        } catch (ConstraintViolationException $exception) {
            return;
        }
        $this->fail();
    }

    /**
     * @dataProvider TupleTooManyValuesDataProvider
     * @param array $tooManyValues
     */
    public function testTupleTooManyValues(array $tooManyValues) : void
    {
        $this->expectNotToPerformAssertions();
        $transformation = new TupleTransformation(
            [
                new IntegerTransformation(),
                new IntegerTransformation()
            ]
        );

        try {
            $result = $transformation->transform($tooManyValues);
        } catch (ConstraintViolationException $exception) {
            return;
        }
        $this->fail();
    }

    public function TupleTooManyValuesDataProvider() : array
    {
        return [
            'too_many_values' => [[1,2,3]]
        ];
    }

    public function TupleFailingTransformationDataProvider() : array
    {
        return [
            'incorrect_tuple' => [[1, 2]]
        ];
    }

    public function TupleTransformationDataProvider() : array
    {
        return [
            'array_test01' => [[1, 2], [1, 2]]
        ];
    }
}
