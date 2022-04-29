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
use ILIAS\Refinery\KindlyTo\Transformation\RecordTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\StringTransformation;
use ILIAS\Tests\Refinery\TestCase;

class RecordTransformationTest extends TestCase
{
    private const STRING_KEY = 'stringKey';
    private const INT_KEY = 'integerKey';
    private const SECOND_INT_KEY = 'integerKey2';

    /**
     * @dataProvider RecordTransformationDataProvider
     * @param array $originVal
     * @param array $expectedVal
     */
    public function testRecordTransformationIsValid(array $originVal, array $expectedVal) : void
    {
        $recTransform = new RecordTransformation(
            [
                self::STRING_KEY => new StringTransformation(),
                self::INT_KEY => new IntegerTransformation()
            ]
        );
        $transformedValue = $recTransform->transform($originVal);
        $this->assertIsArray($transformedValue);
        $this->assertEquals($expectedVal, $transformedValue);
    }

    /**
     * @dataProvider RecordFailureDataProvider
     * @param array $origVal
     */
    public function testRecordTransformationFailures(array $origVal) : void
    {
        $this->expectNotToPerformAssertions();
        $recTransformation = new RecordTransformation(
            [
                self::STRING_KEY => new StringTransformation(),
                self::INT_KEY => new IntegerTransformation()
            ]
        );

        try {
            $result = $recTransformation->transform($origVal);
        } catch (ConstraintViolationException $exception) {
            return;
        }
        $this->fail();
    }

    public function testInvalidArray() : void
    {
        $this->expectNotToPerformAssertions();
        try {
            $recTransformation = new RecordTransformation(
                [
                    new StringTransformation(),
                    new IntegerTransformation()
                ]
            );
        } catch (ConstraintViolationException $exception) {
            return;
        }
        $this->fail();
    }

    /**
     * @dataProvider RecordValueInvalidDataProvider
     * @param array $originalValue
     */
    public function testInvalidValueDoesNotMatch(array $originalValue) : void
    {
        $this->expectNotToPerformAssertions();
        $recTransformation = new RecordTransformation(
            [
                self::INT_KEY => new IntegerTransformation(),
                self::SECOND_INT_KEY => new IntegerTransformation()
            ]
        );

        try {
            $result = $recTransformation->transform($originalValue);
        } catch (ConstraintViolationException $exception) {
            return;
        }
        $this->fail();
    }

    public function RecordTransformationDataProvider() : array
    {
        return [
            "exact_form" => [['stringKey' => 'hello', 'integerKey' => 1], ['stringKey' => 'hello', 'integerKey' => 1]],

            'too_many_values' => [
                ['stringKey' => 'hello', 'integerKey' => 1, 'secondIntKey' => 1],
                ['stringKey' => 'hello', 'integerKey' => 1]
            ]
        ];
    }

    public function RecordFailureDataProvider() : array
    {
        return [
            'too_little_values' => [['stringKey' => 'hello']],
            'key_is_not_a_string' => [['testKey' => 'hello',]],
            'key_value_is_invalid' => [['stringKey' => 'hello', 'integerKey2' => 1]]
        ];
    }

    public function RecordValueInvalidDataProvider() : array
    {
        return [
            'invalid_value' => [['stringKey' => 'hello', 'integerKey2' => 1]]
        ];
    }
}
