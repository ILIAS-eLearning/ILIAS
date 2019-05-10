<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ConstraintViolationExceptionTest extends TestCase
{
	public function testTranslationOfMessage()
	{
		$that = $this;
		$callback = function ($languageId) use ($that) {
			$that->assertEquals('some_key', $languageId);
			return 'Some text "%s" and "%s"';
		};

		try {
			throw new \ILIAS\Refinery\ConstraintViolationException(
				'This is an error message for developers',
				'some_key',
				'Value To Replace',
				'Some important stuff'
			);
		} catch (\ILIAS\Refinery\ConstraintViolationException $exception) {
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
