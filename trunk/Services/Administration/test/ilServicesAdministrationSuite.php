<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilServicesAdministrationSuite extends PHPUnit_Framework_TestSuite
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
