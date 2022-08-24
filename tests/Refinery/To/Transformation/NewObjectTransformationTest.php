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

use Error;
use Exception;
use ILIAS\Data\Result\Ok;
use ILIAS\Refinery\To\Transformation\NewObjectTransformation;
use ILIAS\Tests\Refinery\TestCase;
use TypeError;

class NewObjectTransformationTest extends TestCase
{
    /**
     * @throws \ReflectionException
     */
    public function testNewObjectTransformation(): void
    {
        $transformation = new NewObjectTransformation(MyClass::class);

        $object = $transformation->transform(['hello', 42]);

        $result = $object->myMethod();

        $this->assertEquals(['hello', 42], $result);
    }

    public function testNewObjectTransformationThrowsTypeErrorOnInvalidConstructorArguments(): void
    {
        $this->expectNotToPerformAssertions();

        $transformation = new NewObjectTransformation(MyClass::class);

        try {
            $object = $transformation->transform(['hello', 'world']);
        } catch (TypeError $exception) {
            return;
        }

        $this->fail();
    }

    /**
     * @throws \ReflectionException
     */
    public function testNewObjectApply(): void
    {
        $transformation = new NewObjectTransformation(MyClass::class);

        $resultObject = $transformation->applyTo(new Ok(['hello', 42]));

        $object = $resultObject->value();

        $result = $object->myMethod();

        $this->assertEquals(['hello', 42], $result);
    }

    public function testNewObjectApplyResultsErrorObjectOnInvalidConstructorArguments(): void
    {
        $this->expectNotToPerformAssertions();

        $transformation = new NewObjectTransformation(MyClass::class);

        try {
            $resultObject = $transformation->applyTo(new Ok(['hello', 'world']));
        } catch (Error $error) {
            return;
        }

        $this->fail();
    }

    public function testExceptionInConstructorWillResultInErrorObject(): void
    {
        $transformation = new NewObjectTransformation(MyClassThrowsException::class);

        $resultObject = $transformation->applyTo(new Ok(['hello', 100]));

        $this->assertTrue($resultObject->isError());
    }

    public function testExceptionInConstructorWillThrowException(): void
    {
        $this->expectNotToPerformAssertions();

        $transformation = new NewObjectTransformation(MyClassThrowsException::class);

        try {
            $resultObject = $transformation->transform(['hello', 100]);
        } catch (Exception $exception) {
            return;
        }

        $this->fail();
    }
}

class MyClass
{
    private string $string;
    private int $integer;

    public function __construct(string $string, int $integer)
    {
        $this->string = $string;
        $this->integer = $integer;
    }

    public function myMethod(): array
    {
        return [$this->string, $this->integer];
    }
}

class MyClassThrowsException
{
    public function __construct(string $string, int $integer)
    {
        throw new Exception();
    }
}
