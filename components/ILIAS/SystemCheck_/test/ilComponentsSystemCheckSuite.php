<?php

declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

class ilComponentsSystemCheckSuite extends TestSuite
{
    public static function suite(): self
    {
        $suite = new ilComponentsSystemCheckSuite();

        include_once(substr(__FILE__, 0, strpos(__FILE__, "components/ILIAS")) . "/components/ILIAS/SystemCheck_/test/ilSystemCheckTaskTest.php");
        $suite->addTestSuite(ilSystemCheckTaskTest::class);
        return $suite;
    }
}
