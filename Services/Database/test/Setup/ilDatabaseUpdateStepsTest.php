<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

use ILIAS\Setup\Environment;
use ILIAS\Setup\ObjectiveIterator;
use ILIAS\Setup\Objective;

class Test_ilDatabaseUpdateSteps extends ilDatabaseUpdateSteps
{
    public $called = [];

    public $step_2_precondition = null;

    public function step_1(\ilDBInterface $db)
    {
        $this->called[] = 1;
        // Call some function on the interface to check if this step
        // is really called.
        $db->connect();
    }

    // 4 comes before 2 to check if the class gets the sorting right
    public function step_4(\ilDBInterface $db)
    {
        $this->called[] = 4;
        // Call some function on the interface to check if this step
        // is really called.
        $db->connect();
    }

    public function step_2(\ilDBInterface $db)
    {
        $this->called[] = 2;
        // Call some function on the interface to check if this step
        // is really called.
        $db->connect();
    }

    public function getAdditionalPreconditionsForStep(int $num) : array
    {
        if ($this->step_2_precondition && $num === 2) {
            return [$this->step_2_precondition];
        }
        return [];
    }

    public function _getSteps() : array
    {
        return $this->getSteps();
    }
}

class ilDatabaseUpdateStepsTest extends TestCase
{
    protected function setUp() : void
    {
        $this->base = $this->createMock(Objective::class);

        $this->test1 = new Test_ilDatabaseUpdateSteps($this->base);
    }

    public function testGetStep1()
    {
        $env = $this->createMock(Environment::class);

        $step1 = $this->test1->getStep(1);

        $this->assertInstanceOf(ilDatabaseUpdateStep::class, $step1);
        $this->assertEquals(
            hash("sha256", Test_ilDatabaseUpdateSteps::class . "::step_1"),
            $step1->getHash()
        );

        $preconditions = $step1->getPreconditions($env);

        $this->assertCount(1, $preconditions);
        $this->assertSame($this->base, $preconditions[0]);
    }

    public function testGetStep2()
    {
        $env = $this->createMock(Environment::class);

        $step1 = $this->test1->getStep(1);
        $step2 = $this->test1->getStep(2);

        $this->assertInstanceOf(ilDatabaseUpdateStep::class, $step2);
        $this->assertEquals(
            hash("sha256", Test_ilDatabaseUpdateSteps::class . "::step_2"),
            $step2->getHash()
        );

        $preconditions = $step2->getPreconditions($env);

        $this->assertCount(1, $preconditions);
        $this->assertEquals($step1->getHash(), $preconditions[0]->getHash());
    }

    public function testGetStep2WithAdditionalPrecondition()
    {
        $env = $this->createMock(Environment::class);

        $this->test1->step_2_precondition = new ILIAS\Setup\Objective\NullObjective;

        $step1 = $this->test1->getStep(1);
        $step2 = $this->test1->getStep(2);

        $preconditions = $step2->getPreconditions($env);

        $this->assertCount(2, $preconditions);
        $this->assertEquals($step1->getHash(), $preconditions[0]->getHash());
        $this->assertEquals($this->test1->step_2_precondition, $preconditions[1]);
    }

    public function testGetStep4Finished2()
    {
        $env = $this->createMock(Environment::class);

        $step4 = $this->test1->getStep(4, 2);

        $this->assertInstanceOf(ilDatabaseUpdateStep::class, $step4);
        $this->assertEquals(
            hash("sha256", Test_ilDatabaseUpdateSteps::class . "::step_4"),
            $step4->getHash()
        );

        $preconditions = $step4->getPreconditions($env);

        $this->assertCount(1, $preconditions);
        $this->assertEquals($this->base, $preconditions[0]);
    }

    public function testGetStep4Finished1()
    {
        $env = $this->createMock(Environment::class);

        $step4 = $this->test1->getStep(4, 1);
        $step2 = $this->test1->getStep(2, 1);

        $this->assertInstanceOf(ilDatabaseUpdateStep::class, $step4);
        $this->assertEquals(
            hash("sha256", Test_ilDatabaseUpdateSteps::class . "::step_4"),
            $step4->getHash()
        );

        $preconditions = $step4->getPreconditions($env);

        $this->assertCount(1, $preconditions);
        $this->assertEquals($step2->getHash(), $preconditions[0]->getHash());
    }

    public function testGetAllSteps()
    {
        $steps = $this->test1->_getSteps();

        $expected = [1,2,4];

        $this->assertEquals($expected, array_values($steps));
    }

    public function testAchieveAllSteps()
    {
        $env = $this->createMock(Environment::class);
        $db = $this->createMock(\ilDBInterface::class);

        $env
            ->method("getResource")
            ->will($this->returnValueMap([
                [Environment::RESOURCE_DATABASE, $db],
                [\ilDatabaseUpdateStepExecutionLog::class, null]
            ]));

        $db
            ->expects($this->exactly(3))
            ->method("connect");

        $this->base
            ->method("getPreconditions")
            ->willReturn([]);

        $this->base
            ->expects($this->once())
            ->method("achieve")
            ->with($env)
            ->willReturn($env);

        $i = new ObjectiveIterator($env, $this->test1);
        while ($i->valid()) {
            $current = $i->current();
            $env = $current->achieve($env);
            $i->setEnvironment($env);
            $i->next();
        }

        $this->assertEquals([1,2,4], $this->test1->called);
    }

    public function testAchieveSomeSteps()
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
            ->expects($this->atLeastOnce())
            ->method("getLastFinishedStep")
            ->with(Test_ilDatabaseUpdateSteps::class)
            ->willReturn(1);

        $db
            ->expects($this->exactly(2))
            ->method("connect");

        $this->base
            ->method("getPreconditions")
            ->willReturn([]);

        $this->base
            ->expects($this->once())
            ->method("achieve")
            ->with($env)
            ->willReturn($env);

        $i = new ObjectiveIterator($env, $this->test1);
        while ($i->valid()) {
            $current = $i->current();
            $env = $current->achieve($env);
            $i->setEnvironment($env);
            $i->next();
        }

        $this->assertEquals([2,4], $this->test1->called);
    }
}
