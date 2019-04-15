<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Tests\Refinery\To;

use ILIAS\Data\Alphanumeric;
use ILIAS\Refinery\To\Group;
use ILIAS\Refinery\To\Transformation\BooleanTransformation;
use ILIAS\Refinery\To\Transformation\DictionaryTransformation;
use ILIAS\Refinery\To\Transformation\FloatTransformation;
use ILIAS\Refinery\To\Transformation\IntegerTransformation;
use ILIAS\Refinery\To\Transformation\ListTransformation;
use ILIAS\Refinery\To\Transformation\NewMethodTransformation;
use ILIAS\Refinery\To\Transformation\NewObjectTransformation;
use ILIAS\Refinery\To\Transformation\RecordTransformation;
use ILIAS\Refinery\To\Transformation\StringTransformation;
use ILIAS\Refinery\To\Transformation\TupleTransformation;
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
		$this->basicGroup = new Group();
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
		$transformation = $this->basicGroup->toNew(array(MyClass::class, 'myMethod'));

		$this->assertInstanceOf(NewMethodTransformation::class, $transformation);
	}

	public function testNewMethodTransformationThrowsExceptionBecauseToManyParametersAreGiven()
	{
		$this->expectNotToPerformAssertions();

		try {
			$transformation = $this->basicGroup->toNew(array(MyClass::class, 'myMethod', 'hello'));
		} catch (\InvalidArgumentException $exception) {
			return;
		}

		$this->fail();
	}

	public function testNewMethodTransformationThrowsExceptionBecauseToFewParametersAreGiven()
	{
		$this->expectNotToPerformAssertions();

		try {
			$transformation = $this->basicGroup->toNew(array(MyClass::class));
		} catch (\InvalidArgumentException $exception) {
			return;
		}

		$this->fail();
	}

	/**
	 * @throws \ilException
	 */
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
