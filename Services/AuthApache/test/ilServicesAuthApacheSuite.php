<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */
declare(strict_types=1);

use PHPUnit\Framework\TestSuite;

/**
 * Class ilServicesAuthApacheSuite
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilServicesAuthApacheSuite extends TestSuite
{
	public static function suite()
	{
		$suite = new self();

		require_once './Services/AuthApache/test/ilWhiteListUrlValidatorTest.php';
		$suite->addTestSuite('ilWhiteListUrlValidatorTest');

		return $suite;
	}
}