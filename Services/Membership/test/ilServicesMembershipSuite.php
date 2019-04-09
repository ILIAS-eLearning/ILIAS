<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

class ilServicesMembershipSuite extends TestSuite
{
    public static function suite()
    {
		$suite = new ilServicesMembershipSuite();
		
		include_once("./Services/Membership/test/ilMembershipTest.php");
		$suite->addTestSuite("ilMembershipTest");

		return $suite;
    }
}
?>
