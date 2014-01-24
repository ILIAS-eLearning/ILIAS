<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilServicesObjectSuite extends PHPUnit_Framework_TestSuite
{
    public static function suite()
    {
		$suite = new ilServicesObjectSuite();
		
		include_once("./Services/Object/test/ilObjectDefinitionTest.php");
		$suite->addTestSuite("ilObjectDefinitionTest");

		include_once("./Services/Object/test/ilObjectTest.php");
		$suite->addTestSuite("ilObjectTest");
		
		return $suite;
    }
}
?>
