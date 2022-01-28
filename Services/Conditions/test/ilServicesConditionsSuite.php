<?php declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

class ilServicesConditionsSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new ilServicesConditionsSuite();

        include_once("./Services/Conditions/test/ilConditionsTest.php");
        $suite->addTestSuite(ilConditionsTest::class);
        return $suite;
    }
}
