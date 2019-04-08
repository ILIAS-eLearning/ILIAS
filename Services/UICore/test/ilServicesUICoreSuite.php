<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

class ilServicesUICoreSuite extends TestSuite
{
	public static function suite()
	{
		if (defined('ILIAS_PHPUNIT_CONTEXT'))
		{
			include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
			ilUnitUtil::performInitialisation();
		}
		else
		{
			chdir( dirname( __FILE__ ) );
			chdir('../../../');
		}

		$suite = new ilServicesUICoreSuite();
	
		include_once("./Services/UICore/test/ilTemplateTest.php");
		$suite->addTestSuite("ilTemplateTest");

		return $suite;
	}
}