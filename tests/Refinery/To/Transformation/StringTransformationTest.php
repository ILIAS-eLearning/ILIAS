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
use ILIAS\Refinery\To\Transformation\StringTransformation;
use ILIAS\Tests\Refinery\TestCase;
use UnexpectedValueException;

class StringTransformationTest extends TestCase
{
    private StringTransformation $transformation;

    protected function setUp() : void
    {
        $this->transformation = new StringTransformation();
    }

    public function testStringToStringTransformation() : void
    {
        $transformedValue = $this->transformation->transform('hello');

        $this->assertEquals('hello', $transformedValue);
    }

    public function testIntegerToStringTransformation() : void
    {
        $this->expectNotToPerformAssertions();

        try {
            $transformedValue = $this->transformation->transform(200);
        } catch (UnexpectedValueException $exception) {
            return;
        }

        $this->fail();
    }

    public function testNegativeIntegerToIntegerTransformation() : void
    {
        $this->expectNotToPerformAssertions();

        try {
            $transformedValue = $this->transformation->transform(-200);
        } catch (UnexpectedValueException $exception) {
            return;
        }

        $this->assertEquals('-200', $transformedValue);
    }

    public function testZeroIntegerToIntegerTransformation() : void
    {
        $this->expectNotToPerformAssertions();

        try {
            $transformedValue = $this->transformation->transform(0);
        } catch (UnexpectedValueException $exception) {
            return;
        }

        $this->fail();
    }

    public function testFloatToStringTransformation() : void
    {
        $this->expectNotToPerformAssertions();

        $this->expectNotToPerformAssertions();

        try {
            $transformedValue = $this->transformation->transform(10.5);
        } catch (UnexpectedValueException $exception) {
            return;
        }

        $this->fail();
    }

    public function testPositiveBooleanToStringTransformation() : void
    {
        $this->expectNotToPerformAssertions();

        try {
            $transformedValue = $this->transformation->transform(true);
        } catch (UnexpectedValueException $exception) {
            return;
        }

        $this->fail();
    }

    public function testNegativeBooleanToStringTransformation() : void
    {
        $this->expectNotToPerformAssertions();

        try {
            $transformedValue = $this->transformation->transform(false);
        } catch (UnexpectedValueException $exception) {
            return;
        }

        $this->fail();
    }

    public function testStringToStringApply() : void
    {
        $resultObject = new Result\Ok('hello');

        $transformedObject = $this->transformation->applyTo($resultObject);

        $this->assertEquals('hello', $transformedObject->value());
    }

    public function testPositiveIntegerToIntegerApply() : void
    {
        $resultObject = new Result\Ok(200);

        $transformedObject = $this->transformation->applyTo($resultObject);

        $this->assertTrue($transformedObject->isError());
    }

    public function testNegativeIntegerToIntegerApply() : void
    {
        $resultObject = new Result\Ok(-200);

        $transformedObject = $this->transformation->applyTo($resultObject);

        $this->assertTrue($transformedObject->isError());
    }

    public function testZeroIntegerToIntegerApply() : void
    {
        $resultObject = new Result\Ok(0);

        $transformedObject = $this->transformation->applyTo($resultObject);

        $this->assertTrue($transformedObject->isError());
    }

    public function testFloatToStringApply() : void
    {
        $resultObject = new Result\Ok(10.5);

        $transformedObject = $this->transformation->applyTo($resultObject);

        $this->assertTrue($transformedObject->isError());
    }

    public function testBooleanToStringApply() : void
    {
        $resultObject = new Result\Ok(true);

        $transformedObject = $this->transformation->applyTo($resultObject);

        $this->assertTrue($transformedObject->isError());
    }
}
