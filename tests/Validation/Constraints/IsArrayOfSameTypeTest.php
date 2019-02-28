<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Validation\Constraints;

use ILIAS\Data\Result\Ok;
use ILIAS\Refinery\Validation\Constraint;
use ILIAS\Refinery\Validation\Constraints\IsArrayOfSameType;
use ILIAS\Refinery\Validation\Factory;

require_once("libs/composer/vendor/autoload.php");

class IsArrayOfSameTypeTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var Factory
	 */
	private $factory;

	/**
	 * @var Constraint
	 */
	private $constraint;

	public function setUp() {
		$dataFactory = new \ILIAS\Data\Factory();
		$language = $this->createMock(\ilLanguage::class);
		$this->factory = new Factory($dataFactory, $language);

		$this->constraint = $this->factory->isArrayOfSameType();
	}

	public function testValuesOfSameTypeAreValid()
	{
		$resultObject = $this->constraint->applyTo(new Ok(array('hello', 'world')));
		$this->assertTrue($resultObject->isOK());
	}

	public function testValuesOfSameTypeAreValidInAssociativeArray()
	{
		$resultObject = $this->constraint->applyTo(
			new Ok(
				array(
					'first' => 'hello',
					'second' => 'world'
				)
			)
		);

		$this->assertTrue($resultObject->isOK());
	}

	public function testValuesOfDifferentTypesAreNotValid()
	{
		$resultObject = $this->constraint->applyTo(
			new Ok(
				array(
					'hello',
					'world',
					1000
				)
			)
		);

		$this->assertTrue($resultObject->isError());
	}

	public function testValuesOfDifferentTypesMixedAreNotValid()
	{
		$resultObject = $this->constraint->applyTo(
			new Ok(
				array(
					'hello',
					'world',
					1000,
					'hi'
				)
			)
		);

		$this->assertTrue($resultObject->isError());
	}

	public function testValuesOfDifferentTypesAreNotValidInAssociativeArray()
	{
		$resultObject = $this->constraint->applyTo(
			new Ok(
				array(
					'first' => 'hello',
					'second' => 'world',
					'third' => 1000
				)
			)
		);

		$this->assertTrue($resultObject->isError());
	}

	public function testArrayOfSamClassesIsValid()
	{
		$resultObject = $this->constraint->applyTo(
			new Ok(
				array(
					new IsArrayOfSameExampleClass(),
					new IsArrayOfSameExampleClass(),
				)
			)
		);

		$this->assertTrue($resultObject->isOK());
	}

	public function testArrayOfDifferentClassesIsInvalid()
	{
		$resultObject = $this->constraint->applyTo(
			new Ok(
				array(
					new IsArrayOfSameExampleClass(),
					new IsArrayOfSameAnotherExampleClass(),
				)
			)
		);

		$this->assertTrue($resultObject->isError());
	}

	public function testMixedArrayOfClassesAndOthersIsInvalid()
	{
		$resultObject = $this->constraint->applyTo(
			new Ok(
				array(
					new IsArrayOfSameExampleClass(),
					1,
				)
			)
		);

		$this->assertTrue($resultObject->isError());
	}
}

class IsArrayOfSameExampleClass
{}

class IsArrayOfSameAnotherExampleClass
{}
