<?php
/* Copyright (c) 2020 Luka K. A. Stocker, Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Refinery\KindlyTo\Transformation;

use ILIAS\Refinery\ConstraintViolationException;
use ILIAS\Refinery\KindlyTo\Transformation\IntegerTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\RecordTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\StringTransformation;
use ILIAS\Tests\Refinery\TestCase;

require_once('./libs/composer/vendor/autoload.php');

class RecordTransformationTest extends TestCase
{
    const STRING_KEY = 'stringKey';
    const INT_KEY = 'integerKey';
    const SECOND_INT_KEY = 'integerKey2';

    /**
     * @dataProvider RecordTransformationDataProvider
     * @param $originVal
     * @param $expectedVal
     */
    public function testRecordTransformationIsValid($originVal, $expectedVal)
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
     * @param $origVal
     */
    public function testRecordTransformationFailures($origVal)
    {
        $this->expectNotToPerformAssertions();
        $recTransformation = new RecordTransformation(
            array(
                self::STRING_KEY => new StringTransformation(),
                self::INT_KEY => new IntegerTransformation()
            )
        );

        try {
            $result = $recTransformation->transform($origVal);
        } catch (ConstraintViolationException $exception) {
            return;
        }
        $this->fail();
    }

    public function testInvalidArray()
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
     * @param $originalValue
     */
    public function testInvalidValueDoesNotMatch($originalValue)
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

    public function RecordTransformationDataProvider()
    {
        return [
            "exact_form" => [['stringKey' => 'hello', 'integerKey' => 1], ['stringKey' => 'hello', 'integerKey' => 1]],

            'too_many_values' => [['stringKey' => 'hello', 'integerKey' => 1, 'secondIntKey' => 1],['stringKey' => 'hello', 'integerKey' => 1]]
        ];
    }

    public function RecordFailureDataProvider()
    {
        return [
            'too_little_values' => [['stringKey' => 'hello']],
            'key_is_not_a_string' => [['testKey' => 'hello', ]],
            'key_value_is_invalid' => [['stringKey' => 'hello', 'integerKey2' => 1]]
        ];
    }

    public function RecordValueInvalidDataProvider()
    {
        return [
            'invalid_value' => [array('stringKey' => 'hello', 'integerKey2' => 1)]
        ];
    }
}
