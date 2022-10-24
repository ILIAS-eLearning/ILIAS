<?php

declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

class ilServicesPrivacySecuritySuite extends TestSuite
{
    public static function suite(): ilServicesPrivacySecuritySuite
    {
        $suite = new ilServicesPrivacySecuritySuite();

        include_once("./Services/PrivacySecurity/test/ilPrivacySettingsTest.php");
        $suite->addTestSuite(ilPrivacySettingsTest::class);
        return $suite;
    }
}
