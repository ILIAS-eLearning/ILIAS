<?php declare(strict_types=1);/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

class ilServicesMembershipSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new ilServicesMembershipSuite();

        include_once("./Services/Membership/test/ilWaitingListTest.php");
        $suite->addTestSuite("ilWaitingListTest");

        return $suite;
    }
}
