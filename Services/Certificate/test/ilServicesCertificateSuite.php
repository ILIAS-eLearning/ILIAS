<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once __DIR__ . '/bootstrap.php';
/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilServicesCertificateSuite extends PHPUnit_Framework_TestSuite
{
	/**
	 * @return self
	 */
	public static function suite()
	{
		$suite = new self();

		require_once 'Services/Certificate/test/ilCertificateTypeClassMapTest.php';
		$suite->addTestSuite('ilCertificateTypeClassMapTest');

		require_once 'Services/Certificate/test/ilPageFormatsTest.php';
		$suite->addTestSuite('ilPageFormatsTest');

		require_once 'Services/Certificate/test/ilCoursePlaceholderDescriptionTest.php';
		$suite->addTestSuite('ilCoursePlaceholderDescriptionTest');

		require_once 'Services/Certificate/test/ilDefaultPlaceholderDescriptionTest.php';
		$suite->addTestSuite('ilDefaultPlaceholderDescriptionTest');

		return $suite;
	}
}
