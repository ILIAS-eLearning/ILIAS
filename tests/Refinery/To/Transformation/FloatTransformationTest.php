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

namespace ILIAS\Tests\Refinery\To\Transformation;

use ILIAS\Data\Result;
use ILIAS\Refinery\To\Transformation\FloatTransformation;
use ILIAS\Tests\Refinery\TestCase;
use UnexpectedValueException;

class FloatTransformationTest extends TestCase
{
    private FloatTransformation $transformation;

    protected function setUp(): void
    {
        $this->transformation = new FloatTransformation();
    }

    public function testIntegerToFloatTransformation(): void
    {
        $this->expectNotToPerformAssertions();

        try {
            $transformedValue = $this->transformation->transform(200);
        } catch (UnexpectedValueException $exception) {
            return;
        }

        $this->fail();
    }

    public function testStringToFloatTransformation(): void
    {
        $this->expectNotToPerformAssertions();

        try {
            $transformedValue = $this->transformation->transform('hello');
        } catch (UnexpectedValueException $exception) {
            return;
        }

        $this->fail();
    }

    public function testFloatToFloatTransformation(): void
    {
        $transformedValue = $this->transformation->transform(10.5);

        $this->assertEquals(10.5, $transformedValue);
    }

    public function testNegativeIntegerToFloatTransformation(): void
    {
        $this->expectNotToPerformAssertions();

        try {
            $transformedValue = $this->transformation->transform(-200);
        } catch (UnexpectedValueException $exception) {
            return;
        }

        $this->fail();
    }

    public function testZeroIntegerToFloatTransformation(): void
    {
        $this->expectNotToPerformAssertions();

        try {
            $transformedValue = $this->transformation->transform(0);
        } catch (UnexpectedValueException $exception) {
            return;
        }

        $this->fail();
    }

    public function testZeroFloatToFloatTransformation(): void
    {
        $transformedValue = $this->transformation->transform(0.0);

        $this->assertEquals(0.0, $transformedValue);
    }

    public function testPositiveIntegerToFloatApply(): void
    {
        $resultObject = new Result\Ok(200);

        $transformedObject = $this->transformation->applyTo($resultObject);

        $this->assertTrue($transformedObject->isError());
    }

    public function testNegativeIntegerToFloatApply(): void
    {
        $resultObject = new Result\Ok(-200);

        $transformedObject = $this->transformation->applyTo($resultObject);

        $this->assertTrue($transformedObject->isError());
    }

    public function testZeroIntegerToFloatApply(): void
    {
        $resultObject = new Result\Ok(0);

        $transformedObject = $this->transformation->applyTo($resultObject);

        $this->assertTrue($transformedObject->isError());
    }

    public function testStringToFloatApply(): void
    {
        $resultObject = new Result\Ok('hello');

        $transformedObject = $this->transformation->applyTo($resultObject);

        $this->assertTrue($transformedObject->isError());
    }

    public function testIntegerToFloatApply(): void
    {
        $resultObject = new Result\Ok(200);

        $transformedObject = $this->transformation->applyTo($resultObject);

        $this->assertTrue($transformedObject->isError());
    }

    public function testFloatToFloatApply(): void
    {
        $resultObject = new Result\Ok(10.5);

        $transformedObject = $this->transformation->applyTo($resultObject);

        $this->assertEquals(10.5, $transformedObject->value());
    }

    public function testBooleanToFloatApply(): void
    {
        $resultObject = new Result\Ok(true);

        $transformedObject = $this->transformation->applyTo($resultObject);

        $this->assertTrue($transformedObject->isError());
    }
}
