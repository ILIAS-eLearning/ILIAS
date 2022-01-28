<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

class ilServicesWebServicesSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new ilServicesWebServicesSuite();
        include_once("./Services/WebServices/test/ilRPCServerSettingsTest.php");
        $suite->addTestSuite(ilRPCServerSettingsTest::class);
        return $suite;
    }
}
