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
            ->willReturn(null);

        $this->step->achieve($env);
    }
}
