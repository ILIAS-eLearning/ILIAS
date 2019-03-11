<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Tests\Refinery\KindlyTo\Transformation;

use ILIAS\Data\Result\Ok;
use ILIAS\Refinery\KindlyTo\Transformation\ListTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\StringTransformation;

require_once('./libs/composer/vendor/autoload.php');

class ListTransformationTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @throws \ilException
	 */
	public function testListTransformationIsValid()
	{
		$listTransformation = new ListTransformation(new StringTransformation());

		$result = $listTransformation->transform(array('hello', 'world'));

		$this->assertEquals(array('hello', 'world'), $result);
	}

	public function testListTransformationIsInvalid()
	{
		$listTransformation = new ListTransformation(new StringTransformation());

		$result = $listTransformation->transform(array('hello', 2));

		$this->assertEquals(array('hello', '2'), $result);
	}

	public function testListApplyIsValid()
	{
		$listTransformation = new ListTransformation(new StringTransformation());

		$result = $listTransformation->applyTo(new Ok(array('hello', 'world')));

		$this->assertEquals(array('hello', 'world'), $result->value());
		$this->assertTrue($result->isOK());
	}

	public function testListApplyIsInvalid()
	{
		$listTransformation = new ListTransformation(new StringTransformation());

		$result = $listTransformation->applyTo(new Ok(array('hello', 2)));

		$this->assertEquals(array('hello', '2'), $result->value());
	}
}
