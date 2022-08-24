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
use ILIAS\Refinery\To\Transformation\BooleanTransformation;
use ILIAS\Tests\Refinery\TestCase;
use UnexpectedValueException;

class BooleanTransformationTest extends TestCase
{
    private BooleanTransformation $transformation;

    protected function setUp(): void
    {
        $this->transformation = new BooleanTransformation();
    }

    public function testIntegerToBooleanTransformation(): void
    {
        $this->expectNotToPerformAssertions();

        try {
            $transformedValue = $this->transformation->transform(200);
        } catch (UnexpectedValueException $exception) {
            return;
        }
        $this->fail();
    }

    public function testNegativeIntegerToBooleanTransformation(): void
    {
        $this->expectNotToPerformAssertions();

        try {
            $transformedValue = $this->transformation->transform(-200);
        } catch (UnexpectedValueException $exception) {
            return;
        }
        $this->fail();
    }

    public function testZeroIntegerToBooleanTransformation(): void
    {
        $this->expectNotToPerformAssertions();

        try {
            $transformedValue = $this->transformation->transform(0);
        } catch (UnexpectedValueException $exception) {
            return;
        }
        $this->fail();
    }

    public function testStringToBooleanTransformation(): void
    {
        $this->expectNotToPerformAssertions();

        try {
            $transformedValue = $this->transformation->transform('hello');
        } catch (UnexpectedValueException $exception) {
            return;
        }
        $this->fail();
    }

    public function testFloatToBooleanTransformation(): void
    {
        $this->expectNotToPerformAssertions();

        try {
            $transformedValue = $this->transformation->transform(10.5);
        } catch (UnexpectedValueException $exception) {
            return;
        }
        $this->fail();
    }

    public function testNegativeFloatToBooleanTransformation(): void
    {
        $this->expectNotToPerformAssertions();

        try {
            $transformedValue = $this->transformation->transform(-10.5);
        } catch (UnexpectedValueException $exception) {
            return;
        }
        $this->fail();
    }

    public function testZeroFloatToBooleanTransformation(): void
    {
        $this->expectNotToPerformAssertions();

        try {
            $transformedValue = $this->transformation->transform(0.0);
        } catch (UnexpectedValueException $exception) {
            return;
        }
        $this->fail();
    }

    public function testPositiveBooleanToBooleanTransformation(): void
    {
        $transformedValue = $this->transformation->transform(true);
        $this->assertTrue($transformedValue);
    }

    public function testNegativeBooleanToBooleanTransformation(): void
    {
        $transformedValue = $this->transformation->transform(false);
        $this->assertFalse($transformedValue);
    }

    public function testStringToBooleanApply(): void
    {
        $resultObject = new Result\Ok('hello');

        $transformedObject = $this->transformation->applyTo($resultObject);

        $this->assertTrue($transformedObject->isError());
    }

    public function testPositiveIntegerToBooleanApply(): void
    {
        $resultObject = new Result\Ok(200);

        $transformedObject = $this->transformation->applyTo($resultObject);

        $this->assertTrue($transformedObject->isError());
    }

    public function testNegativeIntegerToBooleanApply(): void
    {
        $resultObject = new Result\Ok(-200);

        $transformedObject = $this->transformation->applyTo($resultObject);

        $this->assertTrue($transformedObject->isError());
    }

    public function testZeroIntegerToBooleanApply(): void
    {
        $resultObject = new Result\Ok(0);

        $transformedObject = $this->transformation->applyTo($resultObject);

        $this->assertTrue($transformedObject->isError());
    }

    public function testFloatToBooleanApply(): void
    {
        $resultObject = new Result\Ok(10.5);

        $transformedObject = $this->transformation->applyTo($resultObject);

        $this->assertTrue($transformedObject->isError());
    }

    public function testBooleanToBooleanApply(): void
    {
        $resultObject = new Result\Ok(true);

        $transformedObject = $this->transformation->applyTo($resultObject);

        $this->assertTrue($transformedObject->value());
    }
}
