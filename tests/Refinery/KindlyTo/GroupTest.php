<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Tests\Refinery\KindlyTo;

use ILIAS\Data\Alphanumeric;
use ILIAS\Refinery\KindlyTo\Group;
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
use ILIAS\Tests\Refinery\TestCase;

require_once('./libs/composer/vendor/autoload.php');

class GroupTest extends TestCase
{
	/**
	 * @var Group
	 */
	private $basicGroup;

	public function setUp() : void
	{
		$dataFactory = new \ILIAS\Data\Factory();

		$this->basicGroup = new Group($dataFactory);
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

	public function testNewObjectTransformation()
	{
		$transformation = $this->basicGroup->toNew((string) MyClass::class);

		$this->assertInstanceOf(NewObjectTransformation::class, $transformation);
	}

	public function testNewMethodTransformation()
	{
		$transformation = $this->basicGroup->toNew(array(new MyClass(), 'myMethod'));

		$this->assertInstanceOf(NewMethodTransformation::class, $transformation);
	}

	public function testNewMethodTransformationThrowsExceptionBecauseToManyParametersAreGiven()
	{
		$this->expectNotToPerformAssertions();

		try {
			$transformation = $this->basicGroup->toNew(array(new MyClass(), 'myMethod', 'hello'));
		} catch (\InvalidArgumentException $exception) {
			return;
		}

		$this->fail();
	}

	public function testNewMethodTransformationThrowsExceptionBecauseToFewParametersAreGiven()
	{
		$this->expectNotToPerformAssertions();

		try {
			$transformation = $this->basicGroup->toNew(array(new MyClass()));
		} catch (\InvalidArgumentException $exception) {
			return;
		}

		$this->fail();
	}

	public function testCreateDataTransformation()
	{
		$transformation = $this->basicGroup->data('alphanumeric');

		$this->assertInstanceOf(NewMethodTransformation::class, $transformation);

		$result = $transformation->transform(array('hello'));

		$this->assertInstanceOf(Alphanumeric::class, $result);
	}
}

class MyClass
{
	public function myMethod()
	{
		return array($this->string, $this->integer);
	}
}
