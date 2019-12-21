<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

class ilDatabaseSetupSuite extends TestSuite
{
    /**
     * @return \ilDatabaseSetupSuite
     */
    public static function suite()
    {
        $suite = new self();

        require_once(__DIR__ . "/ilDatabaseUpdateStepsTest.php");
        $suite->addTestSuite(\ilDatabaseUpdateStepsTest::class);
        require_once(__DIR__ . "/ilDatabaseUpdateStepTest.php");
        $suite->addTestSuite(\ilDatabaseUpdateStepTest::class);

        return $suite;
    }
}
