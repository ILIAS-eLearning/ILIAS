<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilServicesMembershipSuite extends PHPUnit_Framework_TestSuite
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
