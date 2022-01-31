<?php declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

class ilServicesRegistrationSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new self();
        include_once("./Services/Registration/test/ilRegistrationSettingsTest.php");
        $suite->addTestSuite(ilRegistrationSettingsTest::class);
        return $suite;
    }
}
