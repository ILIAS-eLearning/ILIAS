<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilServicesMetaDataSuite extends PHPUnit_Framework_TestSuite
{
    public static function suite()
    {
		$suite = new ilServicesMetaDataSuite();
		
		include_once("./Services/MetaData/test/ilMDTest.php");
		$suite->addTestSuite("ilMDTest");

		return $suite;
    }
}
?>
