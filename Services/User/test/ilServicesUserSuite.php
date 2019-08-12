<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

class ilServicesUserSuite extends TestSuite
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
