<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Tests\Refinery\KindlyTo\Transformation;

require_once('./libs/composer/vendor/autoload.php');

use ILIAS\Data\Result\Ok;
use ILIAS\Refinery\KindlyTo\Transformation\DictionaryTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\StringTransformation;

class DictionaryTransformationTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @throws \ilException
	 */
	public function testDictionaryTransformationValid()
	{
		$transformation = new DictionaryTransformation(new StringTransformation());

		$result = $transformation->transform(array('hello' => 'world'));

		$this->assertEquals(array('hello' => 'world'), $result);
	}

	/**
	 * @expectedException \ilException
	 */
	public function testDictionaryTransformationInvalidBecauseKeyIsNotAString()
	{
		$transformation = new DictionaryTransformation(new StringTransformation());

		$result = $transformation->transform(array('world'));

		$this->fail();
	}

	public function testDictionaryTransformationInvalidBecauseValueIsNotAString()
	{
		$transformation = new DictionaryTransformation(new StringTransformation());

		$result = $transformation->transform(array('hello' => 1));

		$this->assertEquals(array('hello' => '1'), $result);
	}

	public function testDictionaryApplyValid()
	{
		$transformation = new DictionaryTransformation(new StringTransformation());

		$result = $transformation->applyTo(new Ok(array('hello' => 'world')));

		$this->assertEquals(array('hello' => 'world'), $result->value());
	}

	public function testDictionaryApplyInvalidBecauseKeyIsNotAString()
	{
		$transformation = new DictionaryTransformation(new StringTransformation());

		$result = $transformation->applyTo(new Ok(array('world')));

		$this->assertTrue($result->isError());
	}

	public function testDictionaryApplyInvalidBecauseValueIsNotAString()
	{
		$transformation = new DictionaryTransformation(new StringTransformation());

		$result = $transformation->applyTo(new Ok(array('hello' => 1)));

		$this->assertEquals(array('hello' => '1'), $result->value());
	}
}
