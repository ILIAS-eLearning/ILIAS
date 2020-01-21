<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

use ILIAS\Setup\Environment;
use ILIAS\Setup\Objective;

class Test_ilDatabaseUpdateStep extends ilDatabaseUpdateSteps
{
    public function step_1(\ilDBInterface $db)
    {
    }
}

class ilDatabaseUpdateStepTest extends TestCase
{
    protected function setUp() : void
    {
        $this->parent = $this->createMock(Test_ilDatabaseUpdateStep::class);
        $this->precondition = $this->createMock(Objective::class);

        $this->step = new \ilDatabaseUpdateStep(
            $this->parent,
            1,
            $this->precondition,
            $this->precondition
        );
    }

    public function testGetPreconditions()
    {
        $env = $this->createMock(Environment::class);

        $this->assertEquals(
            [$this->precondition, $this->precondition],
            $this->step->getPreconditions($env)
        );
    }

    public function testCallsExecutionLog()
    {
        $env = $this->createMock(Environment::class);
        $log = $this->createMock(\ilDatabaseUpdateStepExecutionLog::class);
        $db = $this->createMock(\ilDBInterface::class);

        $env
            ->method("getResource")
            ->will($this->returnValueMap([
                [Environment::RESOURCE_DATABASE, $db],
                [\ilDatabaseUpdateStepExecutionLog::class, $log]
            ]));

        $log
            ->expects($this->once(), $this->at(0))
            ->method("started")
            ->with(get_class($this->parent), 1);

        $log
            ->expects($this->once(), $this->at(1))
            ->method("finished")
            ->with(get_class($this->parent), 1);

        $this->step->achieve($env);
    }

    public function testCallsMethod()
    {
        $env = $this->createMock(Environment::class);
        $db = $this->createMock(\ilDBInterface::class);

        $env
            ->method("getResource")
            ->will($this->returnValueMap([
                [Environment::RESOURCE_DATABASE, $db],
                [\ilDatabaseUpdateStepExecutionLog::class, null]
            ]));

        $this->parent
            ->expects($this->once())
            ->method("step_1")
            ->with($db)
            ->willReturn($null);

        $this->step->achieve($env);
    }
}
