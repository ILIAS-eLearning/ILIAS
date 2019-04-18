<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

class ilServicesCacheSuite extends TestSuite
{
    public static function suite()
    {
		$suite = new ilServicesCacheSuite();
		
		include_once("./Services/Cache/test/ilCacheTest.php");
		$suite->addTestSuite("ilCacheTest");

		return $suite;
    }
}
?>
