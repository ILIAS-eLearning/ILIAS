<?php

declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

class ilServicesSystemCheckSuite extends TestSuite
{
    public static function suite(): self
    {
        $suite = new ilServicesSystemCheckSuite();

        include_once("./Services/SystemCheck/test/ilSystemCheckTaskTest.php");
        $suite->addTestSuite(ilSystemCheckTaskTest::class);
        return $suite;
    }
}
