<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Tests\Refinery\To\Transformation;

use ILIAS\Data\Result\Ok;
use ILIAS\Refinery\To\Transformation\ListTransformation;
use ILIAS\Refinery\To\Transformation\StringTransformation;
use ILIAS\Refinery\Validation\Factory;

require_once('./libs/composer/vendor/autoload.php');

class ListTransformationTest extends \PHPUnit_Framework_TestCase
{
	private $isArrayOfSameType;

	public function setUp()
	{
		$language = $this->getMockBuilder('ilLanguage')
			->disableOriginalConstructor()
			->getMock();

		$dataFactory = new \ILIAS\Data\Factory();

		$validationFactory = new Factory($dataFactory, $language);
		$this->isArrayOfSameType = $validationFactory->isArrayOfSameType();
	}

	/**
	 * @throws \ilException
	 */
	public function testListTransformationIsValid()
	{
		$listTransformation = new ListTransformation(new StringTransformation(), $this->isArrayOfSameType);

		$result = $listTransformation->transform(array('hello', 'world'));

		$this->assertEquals(array('hello', 'world'), $result);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testListTransformationIsInvalid()
	{
		$listTransformation = new ListTransformation(new StringTransformation(), $this->isArrayOfSameType);

		$result = $listTransformation->transform(array('hello', 2));

		$this->fail();
	}

	public function testListApplyIsValid()
	{
		$listTransformation = new ListTransformation(new StringTransformation(), $this->isArrayOfSameType);

		$result = $listTransformation->applyTo(new Ok(array('hello', 'world')));

		$this->assertEquals(array('hello', 'world'), $result->value());
		$this->assertTrue($result->isOK());
	}

	public function testListApplyIsInvalid()
	{
		$listTransformation = new ListTransformation(new StringTransformation(), $this->isArrayOfSameType);

		$result = $listTransformation->applyTo(new Ok(array('hello', 2)));

		$this->assertTrue($result->isError());
	}
}
