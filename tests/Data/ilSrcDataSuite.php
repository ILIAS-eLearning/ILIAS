<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/**
 * Data Test-Suite
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilSrcDataSuite extends PHPUnit_Framework_TestSuite {
	public static function suite()
	{
		$suite = new ilSrcDataSuite();

		// add each test class of the component
		require_once("./src/Data/test/FactoryTests.php");
		require_once("./src/Data/test/ResultTests.php");

		$suite->addTestSuite("FactoryTests");
		$suite->addTestSuite("ResultTests");

		return $suite;
	}
}