<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once __DIR__ . '/bootstrap.php';

/**
 * @author  <killing@leifos.de>
 */
class ilServicesTasksSuite extends PHPUnit_Framework_TestSuite
{
	public static function suite()
	{

		//PHPUnit_Framework_Error_Deprecated::$enabled = FALSE;

		$suite = new self();

		include_once("./Services/Task/test/ilDerivedTaskTest.php");
		$suite->addTestSuite("ilDerivedTaskTest");

		include_once("./Services/Task/test/ilDerivedTaskFactoryTest.php");
		$suite->addTestSuite("ilDerivedTaskFactoryTest");

		include_once("./Services/Task/test/ilDerivedTaskCollectorTest.php");
		$suite->addTestSuite("ilDerivedTaskCollectorTest");

		return $suite;
	}
}
?>