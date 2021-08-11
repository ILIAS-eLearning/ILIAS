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

        require_once(__DIR__ . "/ilDatabaseUpdateStepsExecutedObjectiveTest.php");
        $suite->addTestSuite(\ilDatabaseUpdateStepsExecutedObjectiveTest::class);

        return $suite;
    }
}
