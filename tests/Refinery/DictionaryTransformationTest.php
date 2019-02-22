<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery;

use ILIAS\Data\Result\Ok;
use ILIAS\Refinery\To\Transformation\DictionaryTransformation;
use ILIAS\Refinery\To\Transformation\StringTransformation;

require_once('./libs/composer/vendor/autoload.php');

class DictionaryTransformationTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var \ILIAS\Refinery\Validation\Factory
	 */
	private $validationFactory;

	public function setUp()
	{
		$language = $this->getMockBuilder('ilLanguage')
			->disableOriginalConstructor()
			->getMock();
		$dataFactory = new \ILIAS\Data\Factory();

		$this->validationFactory = new \ILIAS\Refinery\Validation\Factory($dataFactory, $language);
	}

	/**
	 * @throws \ilException
	 */
	public function testDictionaryTransformationValid()
	{
		$transformation = new DictionaryTransformation(new StringTransformation(), $this->validationFactory);

		$result = $transformation->transform(array('hello' => 'world'));

		$this->assertEquals(array('hello' => 'world'), $result);
	}

	/**
	 * @expectedException \ilException
	 */
	public function testDictionaryTransformationInvalidBecauseKeyIsNotAString()
	{
		$transformation = new DictionaryTransformation(new StringTransformation(), $this->validationFactory);

		$result = $transformation->transform(array('world'));

		$this->fail();
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testDictionaryTransformationInvalidBecauseValueIsNotAString()
	{
		$transformation = new DictionaryTransformation(new StringTransformation(), $this->validationFactory);

		$result = $transformation->transform(array('hello' => 1));

		$this->fail();
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testDictionaryTransformationNonArrayCanNotBeTransformedAndThrowsException()
	{
		$transformation = new DictionaryTransformation(new StringTransformation(), $this->validationFactory);

		$result = $transformation->transform(1);

		$this->fail();
	}

	public function testDictionaryApplyValid()
	{
		$transformation = new DictionaryTransformation(new StringTransformation(), $this->validationFactory);

		$result = $transformation->applyTo(new Ok(array('hello' => 'world')));

		$this->assertEquals(array('hello' => 'world'), $result->value());
	}

	public function testDictionaryApplyInvalidBecauseKeyIsNotAString()
	{
		$transformation = new DictionaryTransformation(new StringTransformation(), $this->validationFactory);

		$result = $transformation->applyTo(new Ok(array('world')));

		$this->assertTrue($result->isError());
	}

	public function testDictionaryApplyInvalidBecauseValueIsNotAString()
	{
		$transformation = new DictionaryTransformation(new StringTransformation(), $this->validationFactory);

		$result = $transformation->applyTo(new Ok(array('hello' => 1)));

		$this->assertTrue($result->isError());
	}

	public function testDictonaryNonArrayToTransformThrowsException()
	{
		$transformation = new DictionaryTransformation(new StringTransformation(), $this->validationFactory);

		$result = $transformation->applyTo(new Ok(1));

		$this->assertTrue($result->isError());
	}
}
