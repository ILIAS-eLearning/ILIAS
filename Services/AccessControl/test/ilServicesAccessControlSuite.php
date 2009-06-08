<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilServicesAccessControlSuite extends PHPUnit_Framework_TestSuite
{
    public static function suite()
    {
		$suite = new ilServicesAccessControlSuite();
		
		include_once("./Services/AccessControl/test/ilRBACTest.php");
		$suite->addTestSuite("ilRBACTest");

		return $suite;
    }
}
?>
