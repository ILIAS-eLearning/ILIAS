<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery;

use ILIAS\Data\Result\Ok;
use ILIAS\Refinery\To\Transformation\NewMethodTransformation;

require_once('./libs/composer/vendor/autoload.php');

class NewMethodTransformationTest extends \PHPUnit_Framework_TestCase
{
	private $instance;

	public function setUp()
	{
		$this->instance = new NewMethodTransformationTestClass();
	}

	/**
	 * @throws \ilException
	 * @throws \ReflectionException
	 */
	public function testNewObjectTransformation()
	{
		$transformation = new NewMethodTransformation($this->instance, 'myMethod');

		$result = $transformation->transform(array('hello', 42));

		$this->assertEquals(array('hello', 42), $result);
	}

	/**
	 * @expectedException \TypeError
	 */
	public function testNewMethodTransformationThrowsTypeErrorOnInvalidConstructorArguments()
	{
		$transformation = new NewMethodTransformation($this->instance, 'myMethod');

		$object = $transformation->transform(array('hello', 'world'));

		$this->fail();
	}

	/**
	 * @expectedException \ilException
	 */
	public function testClassDoesNotExistWillThrowException()
	{
		$transformation = new NewMethodTransformation('BreakdanceMcFunkyPants', 'myMethod');

		$this->fail();
	}

	/**
	 * @expectedException \ilException
	 */
	public function testMethodDoesNotExistOnClassWillThrowException()
	{
		$transformation = new NewMethodTransformation($this->instance, 'someMethod');

		$this->fail();
	}

	/**
	 * @expectedException \ReflectionException
	 */
	public function testPrivateMethodCanNotBeCalledInTransform()
	{
		$transformation = new NewMethodTransformation($this->instance, 'myPrivateMethod');

		$object = $transformation->transform(array('hello', 10));

		$this->fail();
	}

	public function testPrivateMethodCanNotBeCalledInApplyto()
	{
		$transformation = new NewMethodTransformation($this->instance, 'myPrivateMethod');

		$object = $transformation->applyTo(new Ok(array('hello', 10)));

		$this->assertTrue($object->isError());
	}

	/**
	 * @expectedException \Exception
	 */
	public function testMethodThrowsExceptionInTransform()
	{
		$transformation = new NewMethodTransformation($this->instance, 'methodThrowsException');

		$object = $transformation->transform(array('hello', 10));

		$this->fail();
	}

	public function testMethodThrowsExceptionInApplyTo()
	{
		$transformation = new NewMethodTransformation($this->instance, 'methodThrowsException');

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
