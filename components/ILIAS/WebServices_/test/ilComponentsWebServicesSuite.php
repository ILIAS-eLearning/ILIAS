<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

class ilComponentsWebServicesSuite extends TestSuite
{
    public static function suite(): self
    {
        $suite = new ilComponentsWebServicesSuite();
        $suite->addTestSuite(ilRPCServerSettingsTest::class);
        $suite->addTestSuite(ilSoapFunctionsTest::class);

        return $suite;
    }
}
