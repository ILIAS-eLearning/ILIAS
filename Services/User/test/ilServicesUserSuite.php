<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilServicesUserSuite extends PHPUnit_Framework_TestSuite
{
    public static function suite()
    {
		$suite = new ilServicesUserSuite();
		
		include_once("./Services/User/test/ilObjUserTest.php");
		$suite->addTestSuite("ilObjUserTest");

		require_once dirname(__FILE__) . '/ilObjUserPasswordTest.php';
		$suite->addTestSuite('ilObjUserPasswordTest');

		return $suite;
    }
}
?>
