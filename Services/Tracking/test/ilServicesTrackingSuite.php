<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

class ilServicesTrackingSuite extends TestSuite
{
    public static function suite()
    {
		$suite = new ilServicesTrackingSuite();
		
		include_once("./Services/Tracking/test/ilTrackingTest.php");
		$suite->addTestSuite("ilTrackingTest");

		return $suite;
    }
}
?>
