<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilServicesMailSuite extends PHPUnit_Framework_TestSuite
{
	/**
	 * @return self
	 */
	public static function suite()
	{
		$suite = new self();

		require_once 'Services/Mail/test/ilMailAddressTest.php';
		$suite->addTestSuite('ilMailAddressTest');

		require_once 'Services/Mail/test/ilMailAddressTypesTest.php';
		$suite->addTestSuite('ilMailAddressTypesTest');

		return $suite;
	}
}