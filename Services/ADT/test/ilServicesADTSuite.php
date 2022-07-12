<?php declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

class ilServicesADTSuite extends TestSuite
{
    public static function suite() : ilServicesADTSuite
    {
        $suite = new ilServicesADTSuite();

        include_once("./Services/ADT/test/ilADTFactoryTest.php");
        $suite->addTestSuite(ilADTFactoryTest::class);
        return $suite;
    }
}
