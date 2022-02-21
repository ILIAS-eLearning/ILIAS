<?php declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

class ilServicesExportSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new ilServicesExportSuite();

        include_once("./Services/Export/test/ilExportOptionsTest.php");
        $suite->addTestSuite(ilExportOptionsTest::class);
        return $suite;
    }
}
