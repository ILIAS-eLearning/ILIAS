<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilServicesAuthenticationSuite extends PHPUnit_Framework_TestSuite
{
    public static function suite()
    {
		$suite = new ilServicesAuthenticationSuite();
		
		include_once("./Services/Authentication/test/ilSessionTest.php");
		$suite->addTestSuite("ilSessionTest");
		
		return $suite;
    }
}
?>
