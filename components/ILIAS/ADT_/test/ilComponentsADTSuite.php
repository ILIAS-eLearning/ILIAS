<?php

declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

class ilComponentsADTSuite extends TestSuite
{
    public static function suite(): ilComponentsADTSuite
    {
        $suite = new ilComponentsADTSuite();

        include_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . "/components/ILIAS/ADT_/test/ilADTFactoryTest.php");
        $suite->addTestSuite(ilADTFactoryTest::class);
        return $suite;
    }
}
