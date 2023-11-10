<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

class ilServicesWebServicesSuite extends TestSuite
{
    public static function suite(): self
    {
        $suite = new ilServicesWebServicesSuite();
        include_once("./components/ILIAS/WebServices/tests/ilRPCServerSettingsTest.php");
        $suite->addTestSuite(ilRPCServerSettingsTest::class);

        include_once './components/ILIAS/soap/tests/ilSoapFunctionsTest.php';
        $suite->addTestSuite(ilSoapFunctionsTest::class);

        return $suite;
    }
}
