<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Tests\Refinery\To\Transformation;

use ILIAS\Data\Result\Ok;
use ILIAS\DI\Exceptions\Exception;
use ILIAS\Refinery\To\Transformation\NewMethodTransformation;
use ILIAS\Refinery\Validation\Constraints\ConstraintViolationException;
use ILIAS\Tests\Refinery\TestCase;

require_once('./libs/composer/vendor/autoload.php');

class NewMethodTransformationTest extends TestCase
{
	private $instance;

	public function setUp() : void
	{
		$this->instance = new NewMethodTransformationTestClass();
	}

	/**
	 * @throws \ilException
	 * @throws \ReflectionException
	 */
	public function testNewObjectTransformation()
	{
		$transformation = new NewMethodTransformation(new NewMethodTransformationTestClass(), 'myMethod');

		$result = $transformation->transform(array('hello', 42));

		$this->assertEquals(array('hello', 42), $result);
	}

	public function testNewMethodTransformationThrowsTypeErrorOnInvalidConstructorArguments()
	{
		$this->expectNotToPerformAssertions();

		$transformation = new NewMethodTransformation(new NewMethodTransformationTestClass(), 'myMethod');

		try {
			$object = $transformation->transform(array('hello', 'world'));
		} catch (\TypeError $exception) {
			return;
		}

		$this->fail();
	}

	public function testClassDoesNotExistWillThrowException()
	{
		$this->expectNotToPerformAssertions();

		try {
			$transformation = new NewMethodTransformation('BreakdanceMcFunkyPants', 'myMethod');
		} catch (ConstraintViolationException $exception) {
			return;
		}

		$this->fail();
	}

	public function testMethodDoesNotExistOnClassWillThrowException()
	{
		$this->expectNotToPerformAssertions();

		try {
			$transformation = new NewMethodTransformation(new NewMethodTransformationTestClass(), 'someMethod');
		} catch (ConstraintViolationException $exception) {
			return;
		}

		$this->fail();
	}

	public function testPrivateMethodCanNotBeCalledInTransform()
	{
		$this->expectNotToPerformAssertions();

		$transformation = new NewMethodTransformation(new NewMethodTransformationTestClass(), 'myPrivateMethod');

		try {
			$object = $transformation->transform(array('hello', 10));
		} catch  (\Error $error) {
			return;
		}

		$this->fail();
	}

	public function testPrivateMethodCanNotBeCalledInApplyto()
	{
		$this->expectNotToPerformAssertions();

		$transformation = new NewMethodTransformation(new NewMethodTransformationTestClass(), 'myPrivateMethod');
		try {
			$object = $transformation->applyTo(new Ok(array('hello', 10)));
		} catch  (\Error $error) {
			return;
		}

		$this->fail();
	}

	public function testMethodThrowsExceptionInTransform()
	{
		$this->expectNotToPerformAssertions();

		$transformation = new NewMethodTransformation(new NewMethodTransformationTestClass(), 'methodThrowsException');

		try {
			$object = $transformation->transform(array('hello', 10));
		} catch (\Exception $exception) {
			return;
		}

		$this->fail();
	}

	public function testMethodThrowsExceptionInApplyTo()
	{
		$transformation = new NewMethodTransformation(new NewMethodTransformationTestClass(), 'methodThrowsException');

		$object = $transformation->applyTo(new Ok(array('hello', 10)));

		$this->assertTrue($object->isError());
	}
}

class NewMethodTransformationTestClass
{
	public function myMethod(string $string, int $integer)
	{
		return array($string, $integer);
	}

	private function myPrivateMethod(string $string, int $integer)
	{
		return array($string, $integer);
	}

	public function methodThrowsException(string $string, int $integer)
	{
		throw new \Exception('SomeException');
	}
}
