<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

use ILIAS\Setup\Environment;
use ILIAS\Setup\ArrayEnvironment;
use ILIAS\Setup\ObjectiveIterator;
use ILIAS\Setup\Objective;

class Test_ilDatabaseUpdateSteps implements ilDatabaseUpdateSteps
{
    public array $called = [];

    protected ?ilDBInterface $db = null;

    public function prepare(ilDBInterface $db)
    {
        $this->db = $db;
    }


    public function step_1()
    {
        $this->called[] = 1;
        // Call some function on the interface to check if this step
        // is really called.
        $this->db->connect();
    }

    // 4 comes before 2 to check if the class gets the sorting right
    public function step_4()
    {
        $this->called[] = 4;
        // Call some function on the interface to check if this step
        // is really called.
        $this->db->connect();
    }

    public function step_2()
    {
        $this->called[] = 2;
        // Call some function on the interface to check if this step
        // is really called.
        $this->db->connect();
    }
}

class ilDatabaseUpdateStepsExecutedObjectiveTest extends TestCase
{
    public Test_ilDatabaseUpdateSteps $steps;
    public ilDatabaseUpdateStepsExecutedObjective $objective;

    protected function setUp() : void
    {
        $this->steps = new Test_ilDatabaseUpdateSteps;
        $this->objective = new ilDatabaseUpdateStepsExecutedObjective($this->steps);
    }

    public function testCorrectExecutionOrder()
    {
        $execution_log = new class() implements ilDatabaseUpdateStepExecutionLog {
            public function started(string $class, int $step) : void
            {
            }
            public function finished(string $class, int $step) : void
            {
            }
            public function getLastStartedStep(string $class) : int
            {
                return 0;
            }
            public function getLastFinishedStep(string $class) : int
            {
                return 0;
            }
        };
        $db = $this->createMock(\ilDBInterface::class);
        $env = new ArrayEnvironment([
            ilDatabaseUpdateStepExecutionLog::class => $execution_log,
            Environment::RESOURCE_DATABASE => $db
        ]);

        $db->expects($this->exactly(3))
            ->method("connect");

        $this->objective->achieve($env);

        $this->assertEquals([1,2,4], $this->steps->called);
    }

    public function testUsesExecutionLock()
    {
        $execution_log = new class($this) implements ilDatabaseUpdateStepExecutionLog {
            protected $test;//PHP8Review: Missing complex/object typehint

            public function __construct($test)
            {
                $this->test = $test;
            }
            public function started(string $class, int $step) : void
            {
                $this->test->steps->called[] = ["started", $class, $step];
            }
            public function finished(string $class, int $step) : void
            {
                $this->test->steps->called[] = ["finished", $class, $step];
            }
            public function getLastStartedStep(string $class) : int
            {
                return 0;
            }
            public function getLastFinishedStep(string $class) : int
            {
                return 0;
            }
        };
        $db = $this->createMock(\ilDBInterface::class);
        $env = new ArrayEnvironment([
            ilDatabaseUpdateStepExecutionLog::class => $execution_log,
            Environment::RESOURCE_DATABASE => $db
        ]);

        $this->objective->achieve($env);

        $expected = [
            ["started", Test_ilDatabaseUpdateSteps::class, 1],
            1,
            ["finished", Test_ilDatabaseUpdateSteps::class, 1],
            ["started", Test_ilDatabaseUpdateSteps::class, 2],
            2,
            ["finished", Test_ilDatabaseUpdateSteps::class, 2],
            ["started", Test_ilDatabaseUpdateSteps::class, 4],
            4,
            ["finished", Test_ilDatabaseUpdateSteps::class, 4]
        ];

        $this->assertEquals($expected, $this->steps->called);
    }

    public function testOnlyExecuteNonExecutedSteps()
    {
        $execution_log = new class() implements ilDatabaseUpdateStepExecutionLog {
            public function started(string $class, int $step) : void
            {
            }
            public function finished(string $class, int $step) : void
            {
            }
            public function getLastStartedStep(string $class) : int
            {
                return 1;
            }
            public function getLastFinishedStep(string $class) : int
            {
                return 1;
            }
        };
        $db = $this->createMock(\ilDBInterface::class);
        $env = new ArrayEnvironment([
            ilDatabaseUpdateStepExecutionLog::class => $execution_log,
            Environment::RESOURCE_DATABASE => $db
        ]);

        $db->expects($this->exactly(2))
            ->method("connect");

        $this->objective->achieve($env);

        $this->assertEquals([2,4], $this->steps->called);
    }

    public function testExceptionOnNonMatchingStartAndFinished()
    {
        $this->expectException(\RuntimeException::class);

        $execution_log = new class() implements ilDatabaseUpdateStepExecutionLog {
            public function started(string $class, int $step) : void
            {
            }
            public function finished(string $class, int $step) : void
            {
            }
            public function getLastStartedStep(string $class) : int
            {
                return 2;
            }
            public function getLastFinishedStep(string $class) : int
            {
                return 1;
            }
        };
        $db = $this->createMock(\ilDBInterface::class);
        $env = new ArrayEnvironment([
            ilDatabaseUpdateStepExecutionLog::class => $execution_log,
            Environment::RESOURCE_DATABASE => $db
        ]);
        $this->objective->achieve($env);
    }
}
