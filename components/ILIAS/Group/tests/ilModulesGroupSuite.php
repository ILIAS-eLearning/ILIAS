<?php

declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

class ilModulesGroupSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new ilModulesGroupSuite();
        include_once("./components/ILIAS/Group/tests/ilGroupEventHandlerTest.php");
        $suite->addTestSuite(ilGroupEventHandlerTest::class);
        return $suite;
    }
}
