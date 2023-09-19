<?php

declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

class ilComponentsCopyWizardSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new ilComponentsCopyWizardSuite();

        include_once("./components/ILIAS/CopyWizard_/test/ilCopyWizardOptionsTest.php");
        $suite->addTestSuite(ilCopyWizardOptionsTest::class);
        return $suite;
    }
}
