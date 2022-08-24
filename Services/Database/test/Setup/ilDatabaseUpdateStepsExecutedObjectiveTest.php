<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use PHPUnit\Framework\TestCase;

use ILIAS\Setup\Environment;
use ILIAS\Setup\ArrayEnvironment;

class Test_ilDatabaseUpdateSteps implements ilDatabaseUpdateSteps
{
    public array $called = [];

    protected ?ilDBInterface $db = null;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }


    public function step_1(): void
    {
        $this->called[] = 1;
        // Call some function on the interface to check if this step
        // is really called.
        $this->db->connect();
    }

    // 4 comes before 2 to check if the class gets the sorting right
    public function step_4(): void
    {
        $this->called[] = 4;
        // Call some function on the interface to check if this step
        // is really called.
        $this->db->connect();
    }

    public function step_2(): void
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

    protected function setUp(): void
    {
        $this->steps = new Test_ilDatabaseUpdateSteps();
        $this->objective = new ilDatabaseUpdateStepsExecutedObjective($this->steps);
    }

    public function testCorrectExecutionOrder(): void
    {
        $execution_log = new class () implements ilDatabaseUpdateStepExecutionLog {
            public function started(string $class, int $step): void
            {
            }
            public function finished(string $class, int $step): void
            {
            }
            public function getLastStartedStep(string $class): int
            {
                return 0;
            }
            public function getLastFinishedStep(string $class): int
            {
                return 0;
            }
        };
        $steps_reader = new class () extends ilDBStepReader {
        };
        $db = $this->createMock(ilDBInterface::class);
        $env = new ArrayEnvironment([
            ilDBStepReader::class => $steps_reader,
            ilDatabaseUpdateStepExecutionLog::class => $execution_log,
            Environment::RESOURCE_DATABASE => $db
        ]);

        $db->expects($this->exactly(3))
            ->method("connect");

        $this->objective->achieve($env);

        $this->assertEquals([1,2,4], $this->steps->called);
    }

    public function testUsesExecutionLock(): void
    {
        $execution_log = new class ($this) implements ilDatabaseUpdateStepExecutionLog {
            protected ilDatabaseUpdateStepsExecutedObjectiveTest $test;

            public function __construct(ilDatabaseUpdateStepsExecutedObjectiveTest $test)
            {
                $this->test = $test;
            }
            public function started(string $class, int $step): void
            {
                $this->test->steps->called[] = ["started", $class, $step];
            }
            public function finished(string $class, int $step): void
            {
                $this->test->steps->called[] = ["finished", $class, $step];
            }
            public function getLastStartedStep(string $class): int
            {
                return 0;
            }
            public function getLastFinishedStep(string $class): int
            {
                return 0;
            }
        };
        $steps_reader = new class () extends ilDBStepReader {
        };
        $db = $this->createMock(ilDBInterface::class);
        $env = new ArrayEnvironment([
            ilDBStepReader::class => $steps_reader,
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

    public function testOnlyExecuteNonExecutedSteps(): void
    {
        $execution_log = new class () implements ilDatabaseUpdateStepExecutionLog {
            public function started(string $class, int $step): void
            {
            }
            public function finished(string $class, int $step): void
            {
            }
            public function getLastStartedStep(string $class): int
            {
                return 1;
            }
            public function getLastFinishedStep(string $class): int
            {
                return 1;
            }
        };
        $steps_reader = new class () extends ilDBStepReader {
        };
        $db = $this->createMock(ilDBInterface::class);
        $env = new ArrayEnvironment([
            ilDBStepReader::class => $steps_reader,
            ilDatabaseUpdateStepExecutionLog::class => $execution_log,
            Environment::RESOURCE_DATABASE => $db
        ]);

        $db->expects($this->exactly(2))
            ->method("connect");

        $this->objective->achieve($env);

        $this->assertEquals([2,4], $this->steps->called);
    }

    public function testExceptionOnNonMatchingStartAndFinished(): void
    {
        $this->expectException(RuntimeException::class);

        $execution_log = new class () implements ilDatabaseUpdateStepExecutionLog {
            public function started(string $class, int $step): void
            {
            }
            public function finished(string $class, int $step): void
            {
            }
            public function getLastStartedStep(string $class): int
            {
                return 2;
            }
            public function getLastFinishedStep(string $class): int
            {
                return 1;
            }
        };
        $db = $this->createMock(ilDBInterface::class);
        $env = new ArrayEnvironment([
            ilDatabaseUpdateStepExecutionLog::class => $execution_log,
            Environment::RESOURCE_DATABASE => $db
        ]);
        $this->objective->achieve($env);
    }
}
