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

		require_once 'Services/Mail/test/ilMailMimeTest.php';
		$suite->addTestSuite('ilMailMimeTest');

		require_once 'Services/Mail/test/ilMailOptionsTest.php';
		$suite->addTestSuite('ilMailOptionsTest');

		require_once 'Services/Mail/test/ilMailTransportSettingsTest.php';
		$suite->addTestSuite('ilMailTransportSettingsTest');

		require_once 'Services/Mail/test/ilGroupNameAsMailValidatorTest.php';
		$suite->addTestSuite('ilGroupNameAsMailValidatorTest');

		return $suite;
	}
}