<?php

declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

class ilComponentsGroupSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new ilComponentsGroupSuite();
        include_once("./components/ILIAS/Group_/test/ilGroupEventHandlerTest.php");
        $suite->addTestSuite(ilGroupEventHandlerTest::class);
        return $suite;
    }
}
