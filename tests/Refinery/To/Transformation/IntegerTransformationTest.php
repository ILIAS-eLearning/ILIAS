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

use ILIAS\Data\Result;
use ILIAS\Refinery\To\Transformation\IntegerTransformation;
use ILIAS\Tests\Refinery\TestCase;
use UnexpectedValueException;

class IntegerTransformationTest extends TestCase
{
    private IntegerTransformation $transformation;

    protected function setUp() : void
    {
        $this->transformation = new IntegerTransformation();
    }

    public function testIntegerToIntegerTransformation() : void
    {
        $transformedValue = $this->transformation->transform(200);

        $this->assertEquals(200, $transformedValue);
    }

    public function testNegativeIntegerToIntegerTransformation() : void
    {
        $transformedValue = $this->transformation->transform(-200);

        $this->assertEquals(-200, $transformedValue);
    }

    public function testZeroIntegerToIntegerTransformation() : void
    {
        $transformedValue = $this->transformation->transform(0);

        $this->assertEquals(0, $transformedValue);
    }

    public function testStringToIntegerTransformation() : void
    {
        $this->expectNotToPerformAssertions();

        try {
            $transformedValue = $this->transformation->transform('hello');
        } catch (UnexpectedValueException $exception) {
            return;
        }

        $this->fail();
    }

    public function testFloatToIntegerTransformation() : void
    {
        $this->expectNotToPerformAssertions();

        try {
            $transformedValue = $this->transformation->transform(10.5);
        } catch (UnexpectedValueException $exception) {
            return;
        }

        $this->fail();
    }

    public function testPositiveBooleanToIntegerTransformation() : void
    {
        $this->expectNotToPerformAssertions();

        try {
            $transformedValue = $this->transformation->transform(true);
        } catch (UnexpectedValueException $exception) {
            return;
        }

        $this->fail();
    }

    public function testNegativeBooleanToIntegerTransformation() : void
    {
        $this->expectNotToPerformAssertions();

        try {
            $transformedValue = $this->transformation->transform(false);
        } catch (UnexpectedValueException $exception) {
            return;
        }

        $this->fail();
    }

    public function testStringToIntegerApply() : void
    {
        $resultObject = new Result\Ok('hello');

        $transformedObject = $this->transformation->applyTo($resultObject);

        $this->assertTrue($transformedObject->isError());
    }

    public function testPositiveIntegerToIntegerApply() : void
    {
        $resultObject = new Result\Ok(200);

        $transformedObject = $this->transformation->applyTo($resultObject);

        $this->assertEquals(200, $transformedObject->value());
    }

    public function testNegativeIntegerToIntegerApply() : void
    {
        $resultObject = new Result\Ok(-200);

        $transformedObject = $this->transformation->applyTo($resultObject);

        $this->assertEquals(-200, $transformedObject->value());
    }

    public function testZeroIntegerToIntegerApply() : void
    {
        $resultObject = new Result\Ok(0);

        $transformedObject = $this->transformation->applyTo($resultObject);

        $this->assertEquals(0, $transformedObject->value());
    }

    public function testFloatToIntegerApply() : void
    {
        $resultObject = new Result\Ok(10.5);

        $transformedObject = $this->transformation->applyTo($resultObject);

        $this->assertTrue($transformedObject->isError());
    }

    public function testBooleanToIntegerApply() : void
    {
        $resultObject = new Result\Ok(true);

        $transformedObject = $this->transformation->applyTo($resultObject);

        $this->assertTrue($transformedObject->isError());
    }
}
