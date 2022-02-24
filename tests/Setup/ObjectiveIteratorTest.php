<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup;

use ILIAS\Setup;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ObjectiveIteratorTest extends TestCase
{
    public function testBasicAlgorithm() : void
    {
        $hash = "my hash";
        $objective = $this->newObjective($hash);
        $environment = $this->createMock(Setup\Environment::class);

        $objective
            ->expects($this->once())
            ->method("getPreconditions")
            ->with($environment)
            ->willReturn([]);

        $iterator = new Setup\ObjectiveIterator($environment, $objective);

        $this->assertTrue($iterator->valid());
        $this->assertSame($objective, $iterator->current());
        $this->assertSame($hash, $iterator->key());

        $iterator->next();

        $this->assertFalse($iterator->valid());
    }

    public function testRewind() : void
    {
        $hash = "my hash";
        $objective = $this->newObjective($hash);
        $environment = $this->createMock(Setup\Environment::class);

        $iterator = new Setup\ObjectiveIterator($environment, $objective);

        $objective
            ->expects($this->once())
            ->method("getPreconditions")
            ->with($environment)
            ->willReturn([]);

        $iterator->next();
        $iterator->rewind();

        $this->assertTrue($iterator->valid());
        $this->assertSame($objective, $iterator->current());
        $this->assertSame($hash, $iterator->key());
    }

    public function testAllObjectives() : void
    {
        $environment = $this->createMock(Setup\Environment::class);

        $objective1 = $this->newObjective();
        $objective11 = $this->newObjective();
        $objective12 = $this->newObjective();
        $objective121 = $this->newObjective();

        $objective1
            ->method("getPreconditions")
            ->with($environment)
            ->willReturn([$objective11, $objective12]);

        $objective11
            ->method("getPreconditions")
            ->with($environment)
            ->willReturn([]);

        $objective12
            ->method("getPreconditions")
            ->with($environment)
            ->willReturn([$objective121]);

        $objective121
            ->method("getPreconditions")
            ->with($environment)
            ->willReturn([]);

        $iterator = new Setup\ObjectiveIterator($environment, $objective1);

        $expected = [
            $objective11->getHash() => $objective11,
            $objective121->getHash() => $objective121,
            $objective12->getHash() => $objective12,
            $objective1->getHash() => $objective1
        ];

        $this->assertEquals($expected, iterator_to_array($iterator));
    }

    public function testAllObjectivesOnlyReturnsObjectiveOnce() : void
    {
        $environment = $this->createMock(Setup\Environment::class);

        $objective1 = $this->newObjective();
        $objective11 = $this->newObjective();

        $objective1
            ->method("getPreconditions")
            ->with($environment)
            ->willReturn([$objective11, $objective11]);

        $objective11
            ->method("getPreconditions")
            ->with($environment)
            ->willReturn([]);

        $iterator = new Setup\ObjectiveIterator($environment, $objective1);

        $expected = [
            $objective11->getHash() => $objective11,
            $objective1->getHash() => $objective1
        ];
        $this->assertEquals($expected, iterator_to_array($iterator));
    }

    public function testAllObjectivesDetectsCycle() : void
    {
        $environment = $this->createMock(Setup\Environment::class);

        $objective1 = $this->newObjective();
        $objective2 = $this->newObjective();

        $objective1
            ->method("getPreconditions")
            ->with($environment)
            ->willReturn([$objective2]);

        $objective2
            ->method("getPreconditions")
            ->with($environment)
            ->willReturn([$objective1]);

        $this->expectException(Setup\UnachievableException::class);

        $iterator = new Setup\ObjectiveIterator($environment, $objective1);
        iterator_to_array($iterator);
    }

    public function testSetEnvironment() : void
    {
        $env1 = new Setup\ArrayEnvironment([]);
        $env2 = new Setup\ArrayEnvironment([]);

        $objective1 = $this->newObjective();
        $objective2 = $this->newObjective();

        $objective1
            ->expects($this->atLeastOnce())
            ->method("getPreconditions")
            ->with($env1)
            ->willReturn([$objective2]);

        $objective2
            ->expects($this->atLeastOnce())
            ->method("getPreconditions")
            ->with($env2)
            ->willReturn([]);

        $iterator = new Setup\ObjectiveIterator($env1, $objective1);

        $iterator->setEnvironment($env2);
        $iterator->next();
    }

    public function testMarkFailed() : void
    {
        $this->expectException(Setup\UnachievableException::class);

        $env = new Setup\ArrayEnvironment([]);

        $objective_fail = $this->newObjective();
        $objective_1 = $this->newObjective();
        $objective_2 = $this->newObjective();
        $objective_3 = $this->newObjective();

        $objective_1
            ->method("getPreconditions")
            ->willReturn([]);

        $objective_2
            ->method("getPreconditions")
            ->willReturn([]);

        $objective_3
            ->method("getPreconditions")
            ->willReturn([$objective_1, $objective_fail, $objective_2]);

        $iterator = new Setup\ObjectiveIterator($env, $objective_3);


        $this->assertEquals($objective_1, $iterator->current());
        $iterator->next();
        $this->assertEquals($objective_fail, $iterator->current());
        $iterator->markAsFailed($objective_fail);
        $iterator->next();
        $this->assertEquals($objective_2, $iterator->current());
        $iterator->next();
    }

    protected function newObjective($hash = null) : MockObject
    {
        static $no = 0;

        $objective = $this
            ->getMockBuilder(Setup\Objective::class)
            ->setMethods(["getHash", "getLabel", "isNotable", "withResourcesFrom", "getPreconditions", "achieve", "isApplicable"])
            ->setMockClassName("Mock_ObjectiveNo" . ($no++))
            ->getMock();

        $objective
            ->method("getHash")
            ->willReturn($hash ?? "" . $no);

        return $objective;
    }

    public function testFailedPreconditionWithOtherOnStack() : void
    {
        $this->expectException(Setup\UnachievableException::class);

        $env = new Setup\ArrayEnvironment([]);

        $objective_fail = $this->newObjective();
        $objective_1 = $this->newObjective();
        $objective_2 = $this->newObjective();
        $objective_3 = $this->newObjective();

        $objective_1
            ->method("getPreconditions")
            ->willReturn([$objective_fail]);
        $objective_2
            ->method("getPreconditions")
            ->willReturn([]);
        $objective_3
            ->method("getPreconditions")
            ->willReturn([$objective_1, $objective_2]);

        $iterator = new class($env, $objective_3, $objective_fail) extends Setup\ObjectiveIterator {
            public function __construct(
                Setup\Environment $environment,
                Setup\Objective $objective,
                MockObject $objective_fail
            ) {
                parent::__construct($environment, $objective);
                $this->failed[$objective_fail->getHash()] = true;
            }
        };

        $this->assertEquals($objective_fail, $iterator->current());
        $iterator->next();
        $iterator->next();
    }

    public function testFailedPreconditionLastOnStack() : void
    {
        $this->expectException(Setup\UnachievableException::class);

        $env = new Setup\ArrayEnvironment([]);

        $objective_fail = $this->newObjective();
        $objective_1 = $this->newObjective();
        $objective_2 = $this->newObjective();

        $objective_1
            ->method("getPreconditions")
            ->willReturn([$objective_fail]);
        $objective_2
            ->method("getPreconditions")
            ->willReturn([$objective_1]);

        $iterator = new class($env, $objective_2, $objective_fail) extends Setup\ObjectiveIterator {
            public function __construct(
                Setup\Environment $environment,
                Setup\Objective $objective,
                MockObject $objective_fail
            ) {
                parent::__construct($environment, $objective);
                $this->failed[$objective_fail->getHash()] = true;
            }
        };

        $this->assertEquals($objective_fail, $iterator->current());
        $iterator->next();
    }
}
