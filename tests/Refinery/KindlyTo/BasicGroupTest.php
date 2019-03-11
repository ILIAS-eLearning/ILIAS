<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Tests\Refinery\KindlyTo;

use ILIAS\Refinery\KindlyTo\BasicGroup;
use ILIAS\Refinery\KindlyTo\Transformation\BooleanTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\DictionaryTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\FloatTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\IntegerTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\ListTransformation;
use ILIAS\Refinery\To\Transformation\NewMethodTransformation;
use ILIAS\Refinery\To\Transformation\NewObjectTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\RecordTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\StringTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\TupleTransformation;
use ILIAS\Refinery\Validation\Factory;

class BasicGroupTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var BasicGroup
	 */
	private $basicGroup;

	public function setUp()
	{
		$language = $this->getMockBuilder('ilLanguage')
			->disableOriginalConstructor()
			->getMock();

		$dataFactory = new \ILIAS\Data\Factory();

		$validationFactory = new Factory($dataFactory, $language);
		$this->basicGroup = new BasicGroup($validationFactory->isArrayOfSameType());
	}

	public function testIsIntegerTransformationInstance()
	{
		$transformation = $this->basicGroup->int();

		$this->assertInstanceOf(IntegerTransformation::class, $transformation);
	}

	public function testIsStringTransformationInstance()
	{
		$transformation = $this->basicGroup->string();

		$this->assertInstanceOf(StringTransformation::class, $transformation);
	}

	public function testIsFloatTransformationInstance()
	{
		$transformation = $this->basicGroup->float();

		$this->assertInstanceOf(FloatTransformation::class, $transformation);
	}

	public function testIsBooleanTransformationInstance()
	{
		$transformation = $this->basicGroup->bool();

		$this->assertInstanceOf(BooleanTransformation::class, $transformation);
	}

	public function testListOfTransformation()
	{
		$transformation = $this->basicGroup->listOf(new StringTransformation());

		$this->assertInstanceOf(ListTransformation::class, $transformation);
	}

	public function testTupleOfTransformation()
	{
		$transformation = $this->basicGroup->tupleOf(array(new StringTransformation()));

		$this->assertInstanceOf(TupleTransformation::class, $transformation);
	}

	/**
	 * @throws \ilException
	 */
	public function testRecordOfTransformation()
	{
		$transformation = $this->basicGroup->recordOf(array('toString' => new StringTransformation()));

		$this->assertInstanceOf(RecordTransformation::class, $transformation);
	}

	public function testDictionaryOfTransformation()
	{
		$transformation = $this->basicGroup->dictOf(new StringTransformation());

		$this->assertInstanceOf(DictionaryTransformation::class, $transformation);
	}

	/**
	 * @throws \ilException
	 */
	public function testNewObjectTransformation()
	{
		$transformation = $this->basicGroup->toNew((string) MyClass::class);

		$this->assertInstanceOf(NewObjectTransformation::class, $transformation);
	}

	/**
	 * @throws \ilException
	 */
	public function testNewMethodTransformation()
	{
		$transformation = $this->basicGroup->toNew(array(new MyClass(), 'myMethod'));

		$this->assertInstanceOf(NewMethodTransformation::class, $transformation);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 * @throws \ilException
	 */
	public function testNewMethodTransformationThrowsExceptionBecauseToManyParametersAreGiven()
	{
		$transformation = $this->basicGroup->toNew(array(new MyClass(), 'myMethod', 'hello'));

		$this->assertInstanceOf(NewMethodTransformation::class, $transformation);
	}

	/**
	 * @expectedException  \InvalidArgumentException
	 * @throws \ilException
	 */
	public function testNewMethodTransformationThrowsExceptionBecauseToFewParametersAreGiven()
	{
		$transformation = $this->basicGroup->toNew(array(new MyClass()));

		$this->assertInstanceOf(NewMethodTransformation::class, $transformation);
	}
}

class MyClass
{
	public function myMethod()
	{
		return array($this->string, $this->integer);
	}
}
