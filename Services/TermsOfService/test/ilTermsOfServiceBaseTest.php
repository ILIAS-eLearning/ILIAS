<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceBaseTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceBaseTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @param string $exceptionClass
	 */
	protected function assertException(string $exceptionClass)
	{
		if (version_compare(\PHPUnit_Runner_Version::id(), '5.0', '>=')) {
			$this->setExpectedException($exceptionClass);
		}
	}
}