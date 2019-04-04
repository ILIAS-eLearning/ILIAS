<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ConstraintViolationExceptionTest extends PHPUnit_Framework_TestCase
{
	public function testTranslationOfMessage()
	{
		$that = $this;
		$callback = function ($languageId) use ($that) {
			$that->assertEquals('some_key', $languageId);
			return 'Some text "%s" and "%s"';
		};

		try {
			throw new \ILIAS\Refinery\Validation\Constraints\ConstraintViolationException(
				'This is an error message for developers',
				'some_key',
				'Value To Replace',
				'Some important stuff'
			);
		} catch (\ILIAS\Refinery\Validation\Constraints\ConstraintViolationException $exception) {
			$this->assertEquals(
				'Some text "Value To Replace" and "Some important stuff"',
				$exception->getTranslatedMessage($callback)
			);

			$this->assertEquals(
				'This is an error message for developers',
				$exception->getMessage()
			);
		}
	}
}
