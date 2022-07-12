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

namespace ILIAS\Tests\Refinery\To\Transformation;

use ILIAS\Data\Result\Ok;
use ILIAS\Refinery\To\Transformation\IntegerTransformation;
use ILIAS\Refinery\To\Transformation\RecordTransformation;
use ILIAS\Refinery\To\Transformation\StringTransformation;
use ILIAS\Tests\Refinery\TestCase;
use UnexpectedValueException;

class RecordTransformationTest extends TestCase
{
    /**
     * @throws \ilException
     */
    public function testTransformationIsCorrect() : void
    {
        $recordTransformation = new RecordTransformation(
            [
                'stringTrafo' => new StringTransformation(),
                'integerTrafo' => new IntegerTransformation()
            ]
        );

        $result = $recordTransformation->transform(['stringTrafo' => 'hello', 'integerTrafo' => 1]);

        $this->assertEquals(['stringTrafo' => 'hello', 'integerTrafo' => 1], $result);
    }

    public function testInvalidTransformationArray() : void
    {
        $this->expectNotToPerformAssertions();

        try {
            $recordTransformation = new RecordTransformation(
                [
                    new StringTransformation(),
                    new IntegerTransformation()
                ]
            );
        } catch (UnexpectedValueException $exception) {
            return;
        }

        $this->fail();
    }

    public function testTransformationIsInvalidBecauseValueDoesNotMatchWithTransformation() : void
    {
        $this->expectNotToPerformAssertions();

        $recordTransformation = new RecordTransformation(
            [
                'integerTrafo' => new IntegerTransformation(),
                'anotherIntTrafo' => new IntegerTransformation()
            ]
        );

        try {
            $result = $recordTransformation->transform(['stringTrafo' => 'hello', 'anotherIntTrafo' => 1]);
        } catch (UnexpectedValueException $exception) {
            return;
        }

        $this->fail();
    }

    public function testInvalidValueKey() : void
    {
        $this->expectNotToPerformAssertions();

        $recordTransformation = new RecordTransformation(
            [
                'stringTrafo' => new StringTransformation(),
                'integerTrafo' => new IntegerTransformation()
            ]
        );

        try {
            $result = $recordTransformation->transform(['stringTrafo' => 'hello', 'floatTrafo' => 1]);
        } catch (UnexpectedValueException $exception) {
            return;
        }

        $this->fail();
    }

    public function testInvalidToManyValues() : void
    {
        $this->expectNotToPerformAssertions();

        $recordTransformation = new RecordTransformation(
            [
                'stringTrafo' => new StringTransformation(),
                'integerTrafo' => new IntegerTransformation()
            ]
        );


        try {
            $result = $recordTransformation->transform(
                [
                    'stringTrafo' => 'hello',
                    'integerTrafo' => 1,
                    'floatTrafo' => 1
                ]
            );
        } catch (UnexpectedValueException $exception) {
            return;
        }

        $this->fail();
    }

    public function testTransformationThrowsExceptionBecauseKeyIsNotAString() : void
    {
        $this->expectNotToPerformAssertions();

        $recordTransformation = new RecordTransformation(
            [
                'stringTrafo' => new StringTransformation(),
                'integerTrafo' => new IntegerTransformation()
            ]
        );

        try {
            $result = $recordTransformation->transform(['someKey' => 'hello', 1]);
        } catch (UnexpectedValueException $exception) {
            return;
        }

        $this->fail();
    }

    /**
     * @throws \ilException
     */
    public function testApplyIsCorrect() : void
    {
        $recordTransformation = new RecordTransformation(
            [
                'stringTrafo' => new StringTransformation(),
                'integerTrafo' => new IntegerTransformation()
            ]
        );

        $result = $recordTransformation->applyTo(new Ok(['stringTrafo' => 'hello', 'integerTrafo' => 1]));

        $this->assertEquals(['stringTrafo' => 'hello', 'integerTrafo' => 1], $result->value());
    }

    /**
     * @throws \ilException
     */
    public function testApplyIsInvalidBecauseValueDoesNotMatchWithTransformation() : void
    {
        $recordTransformation = new RecordTransformation(
            [
                'integerTrafo' => new IntegerTransformation(),
                'anotherIntTrafo' => new IntegerTransformation()
            ]
        );

        $result = $recordTransformation->applyTo(new Ok(['stringTrafo' => 'hello', 'anotherIntTrafo' => 1]));

        $this->assertTrue($result->isError());
    }

    /**
     * @throws \ilException
     */
    public function testInvalidValueKeyInApplyToMethod() : void
    {
        $recordTransformation = new RecordTransformation(
            [
                'stringTrafo' => new StringTransformation(),
                'integerTrafo' => new IntegerTransformation()
            ]
        );

        $result = $recordTransformation->applyTo(new Ok(['stringTrafo' => 'hello', 'floatTrafo' => 1]));

        $this->assertTrue($result->isError());
    }

    /**
     * @throws \ilException
     */
    public function testInvalidToManyValuesInApplyToMethodCall() : void
    {
        $recordTransformation = new RecordTransformation(
            [
                'stringTrafo' => new StringTransformation(),
                'integerTrafo' => new IntegerTransformation()
            ]
        );

        $result = $recordTransformation->applyTo(
            new Ok(
                [
                    'stringTrafo' => 'hello',
                    'integerTrafo' => 1,
                    'floatTrafo' => 1
                ]
            )
        );

        $this->assertTrue($result->isError());
    }

    /**
     * @throws \ilException
     */
    public function testApplyThrowsExceptionBecauseKeyIsNotAString() : void
    {
        $recordTransformation = new RecordTransformation(
            [
                'stringTrafo' => new StringTransformation(),
                'integerTrafo' => new IntegerTransformation()
            ]
        );

        $result = $recordTransformation->applyTo(new Ok(['someKey' => 'hello', 1]));

        $this->assertTrue($result->isError());
    }
}
