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

use Error;
use ILIAS\Data\Result\Ok;
use ILIAS\DI\Exceptions\Exception;
use ILIAS\Refinery\To\Transformation\NewMethodTransformation;
use ILIAS\Tests\Refinery\TestCase;
use TypeError;
use InvalidArgumentException;

class NewMethodTransformationTest extends TestCase
{
    /**
     * @throws \ilException
     * @throws \ReflectionException
     */
    public function testNewObjectTransformation() : void
    {
        $transformation = new NewMethodTransformation(new NewMethodTransformationTestClass(), 'myMethod');

        $result = $transformation->transform(['hello', 42]);

        $this->assertEquals(['hello', 42], $result);
    }

    public function testNewMethodTransformationThrowsTypeErrorOnInvalidConstructorArguments() : void
    {
        $this->expectNotToPerformAssertions();

        $transformation = new NewMethodTransformation(new NewMethodTransformationTestClass(), 'myMethod');

        try {
            $object = $transformation->transform(['hello', 'world']);
        } catch (TypeError $exception) {
            return;
        }

        $this->fail();
    }

    public function testClassDoesNotExistWillThrowException() : void
    {
        $this->expectNotToPerformAssertions();

        try {
            $transformation = new NewMethodTransformation('BreakdanceMcFunkyPants', 'myMethod');
        } catch (Error $exception) {
            return;
        }

        $this->fail();
    }

    public function testMethodDoesNotExistOnClassWillThrowException() : void
    {
        $this->expectNotToPerformAssertions();

        try {
            $transformation = new NewMethodTransformation(new NewMethodTransformationTestClass(), 'someMethod');
        } catch (InvalidArgumentException $exception) {
            return;
        }

        $this->fail();
    }

    public function testPrivateMethodCanNotBeCalledInTransform() : void
    {
        $this->expectNotToPerformAssertions();

        $transformation = new NewMethodTransformation(new NewMethodTransformationTestClass(), 'myPrivateMethod');

        try {
            $object = $transformation->transform(['hello', 10]);
        } catch (Error $error) {
            return;
        }

        $this->fail();
    }

    public function testPrivateMethodCanNotBeCalledInApplyto() : void
    {
        $this->expectNotToPerformAssertions();

        $transformation = new NewMethodTransformation(new NewMethodTransformationTestClass(), 'myPrivateMethod');
        try {
            $object = $transformation->applyTo(new Ok(['hello', 10]));
        } catch (Error $error) {
            return;
        }

        $this->fail();
    }

    public function testMethodThrowsExceptionInTransform() : void
    {
        $this->expectNotToPerformAssertions();

        $transformation = new NewMethodTransformation(new NewMethodTransformationTestClass(), 'methodThrowsException');

        try {
            $object = $transformation->transform(['hello', 10]);
        } catch (Exception $exception) {
            return;
        }

        $this->fail();
    }

    public function testMethodThrowsExceptionInApplyTo() : void
    {
        $transformation = new NewMethodTransformation(new NewMethodTransformationTestClass(), 'methodThrowsException');

        $object = $transformation->applyTo(new Ok(['hello', 10]));

        $this->assertTrue($object->isError());
    }
}

class NewMethodTransformationTestClass
{
    public function myMethod(string $string, int $integer) : array
    {
        return [$string, $integer];
    }

    private function myPrivateMethod(string $string, int $integer) : array
    {
        return [$string, $integer];
    }

    public function methodThrowsException(string $string, int $integer) : void
    {
        throw new Exception('SomeException');
    }
}
