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
use ILIAS\Refinery\Validation\Constraints\ConstraintViolationException;
use ILIAS\Tests\Refinery\TestCase;

class DictionaryTransformationTest extends TestCase
{
	public function testDictionaryTransformationValid()
	{
		$transformation = new DictionaryTransformation(new StringTransformation());

		$result = $transformation->transform(array('hello' => 'world'));

		$this->assertEquals(array('hello' => 'world'), $result);
	}

	public function testDictionaryTransformationInvalidBecauseKeyIsNotAString()
	{
		$this->expectNotToPerformAssertions();

		$transformation = new DictionaryTransformation(new StringTransformation());

		try {
			$result = $transformation->transform(array('world'));
		} catch (ConstraintViolationException $exception) {
			return;
		}

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
