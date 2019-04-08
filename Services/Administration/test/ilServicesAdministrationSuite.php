<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

class ilServicesAdministrationSuite extends TestSuite
{
    public static function suite()
    {
		$suite = new ilServicesAdministrationSuite();
		
		include_once("./Services/Administration/test/ilSettingTest.php");
		$suite->addTestSuite("ilSettingTest");
		
		return $suite;
    }
}
?>
