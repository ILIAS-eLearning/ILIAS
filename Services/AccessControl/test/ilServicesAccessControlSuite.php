<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

class ilServicesAccessControlSuite extends TestSuite
{
    public static function suite()
    {
		
		PHPUnit\Framework\Error\Deprecated::$enabled = FALSE;
		
		$suite = new ilServicesAccessControlSuite();
		
		include_once("./Services/AccessControl/test/ilRBACTest.php");
		$suite->addTestSuite("ilRBACTest");

		return $suite;
    }
}
?>
