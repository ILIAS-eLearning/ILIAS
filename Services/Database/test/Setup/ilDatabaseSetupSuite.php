<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

class ilDatabaseSetupSuite extends TestSuite
{
    public static function suite() : ilDatabaseSetupSuite
    {
        $suite = new self();

        require_once(__DIR__ . "/ilDatabaseUpdateStepsExecutedObjectiveTest.php");
        $suite->addTestSuite(ilDatabaseUpdateStepsExecutedObjectiveTest::class);

        require_once(__DIR__ . "/ilDBStepReaderTest.php");
        $suite->addTestSuite(ilDBStepReaderTest::class);

        require_once(__DIR__ . "/ilDBStepExecutionDBTest.php");
        $suite->addTestSuite(ilDBStepExecutionDBTest::class);

        require_once __DIR__ . "/ilDatabaseUpdateStepsMetricsCollectedObjectiveTest.php";
        $suite->addTestSuite(ilDatabaseUpdateStepsMetricsCollectedObjectiveTest::class);

        return $suite;
    }
}
