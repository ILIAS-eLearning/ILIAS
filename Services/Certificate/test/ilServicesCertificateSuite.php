<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

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

		return $suite;
	}
}
