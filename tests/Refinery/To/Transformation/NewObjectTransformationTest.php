<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Tests\Refinery\To\Transformation;

use ILIAS\Data\Result\Ok;
use ILIAS\Refinery\To\Transformation\NewObjectTransformation;
use ILIAS\Tests\Refinery\TestCase;

require_once('./libs/composer/vendor/autoload.php');

class NewObjectTransformationTest extends TestCase
{
	/**
	 * @throws \ReflectionException
	 */
	public function testNewObjectTransformation()
	{
		$transformation = new NewObjectTransformation(MyClass::class);

		$object = $transformation->transform(array('hello', 42));

		$result = $object->myMethod();

		$this->assertEquals(array('hello', 42), $result);
	}

	/**
	 * @expectedException \TypeError
	 */
	public function testNewObjectTransformationThrowsTypeErrorOnInvalidConstructorArguments()
	{
		$transformation = new NewObjectTransformation(MyClass::class);

		$object = $transformation->transform(array('hello', 'world'));

		$this->fail();
	}

	/**
	 * @throws \ReflectionException
	 */
	public function testNewObjectApply()
	{
		$transformation = new NewObjectTransformation(MyClass::class);

		$resultObject = $transformation->applyTo(new Ok(array('hello', 42)));

		$object = $resultObject->value();

		$result = $object->myMethod();

		$this->assertEquals(array('hello', 42), $result);
	}

	public function testNewObjectApplyResultsErrorObjectOnInvalidConstructorArguments()
	{
		$transformation = new NewObjectTransformation(MyClass::class);

		$resultObject = $transformation->applyTo(new Ok(array('hello', 'world')));

		$this->assertTrue($resultObject->isError());
	}

	public function testExceptionInConstructorWillResultInErrorObject()
	{
		$transformation = new NewObjectTransformation(MyClassThrowsException::class);

		$resultObject = $transformation->applyTo(new Ok(array('hello', 100)));

		$this->assertTrue($resultObject->isError());
	}

	/**
	 * @expectedException \Exception
	 */
	public function testExceptionInConstructorWillThrowException()
	{
		$transformation = new NewObjectTransformation(MyClassThrowsException::class);

		$resultObject = $transformation->transform(array('hello', 100));

		$this->fail();
	}
}

class MyClass
{
	private $string;

	private $integer;

	public function __construct(string $string, int $integer)
	{
		$this->string = $string;
		$this->integer = $integer;
	}

	public function myMethod()
	{
		return array($this->string, $this->integer);
	}
}

class MyClassThrowsException
{
	public function __construct(string $string, int $integer)
	{
		throw new \Exception();
	}
}
