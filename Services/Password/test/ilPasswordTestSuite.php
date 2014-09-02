<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Utilities/classes/class.ilUtil.php';
require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';

/**
 * Class ilPasswordTestSuite
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ServicesPassword
 */
class ilPasswordTestSuite extends PHPUnit_Framework_TestSuite
{
	/**
	 * @return ilPasswordTestSuite
	 */
	public static function suite()
	{
		// Set timezone to prevent notices
		date_default_timezone_set('Europe/Berlin');

		$suite = new self();

		require_once dirname(__FILE__) . '/encoders/ilMd5PasswordEncoderTest.php';
		$suite->addTestSuite('ilMd5PasswordEncoderTest');

		require_once dirname(__FILE__) . '/encoders/ilBcryptPasswordEncoderTest.php';
		$suite->addTestSuite('ilBcryptPasswordEncoderTest');

		return $suite;
	}
} 