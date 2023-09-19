<?php

declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

class ilComponentsPrivacySecuritySuite extends TestSuite
{
    public static function suite(): ilComponentsPrivacySecuritySuite
    {
        $suite = new ilComponentsPrivacySecuritySuite();

        include_once("./components/ILIAS/PrivacySecurity_/test/ilPrivacySettingsTest.php");
        $suite->addTestSuite(ilPrivacySettingsTest::class);
        return $suite;
    }
}
