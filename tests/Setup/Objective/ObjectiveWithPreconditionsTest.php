<?php declare(strict_types=1);

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup\Objective;

use ILIAS\Setup;
use ILIAS\Setup\Objective;
use ILIAS\Tests\Setup as Test;
use PHPUnit\Framework\TestCase;

class ObjectiveWithPreconditionsTest extends TestCase
{
    use Test\Helper;

    protected Setup\Objective $objective;
    protected Setup\Objective $precondition;
    protected Objective\ObjectiveWithPreconditions $with_precondition;

    public function setUp() : void
    {
        $this->objective = $this->newObjective();
        $this->precondition = $this->newObjective();

        $this->with_precondition = new Objective\ObjectiveWithPreconditions(
            $this->objective,
            $this->precondition
        );
    }

    public function testGetHash() : void
    {
        $this->assertEquals($this->objective->getHash(), $this->with_precondition->getHash());
    }

    public function testGetLabel() : void
    {
        $label = "some_label";

        $this->objective
            ->expects($this->once())
            ->method("getLabel")
            ->willReturn($label);

        $this->assertEquals($label, $this->with_precondition->getLabel());
    }

    public function testIsNotable() : void
    {
        $notable = true;

        $this->objective
            ->expects($this->once())
            ->method("isNotable")
            ->willReturn($notable);

        $this->assertEquals($notable, $this->with_precondition->isNotable());
    }

    public function testGetPreconditions() : void
    {
        $another = $this->newObjective();

        $env = $this->createMock(Setup\Environment::class);

        $this->objective
            ->expects($this->once())
            ->method("getPreconditions")
            ->with($env)
            ->willReturn([$another]);

        $pre = $this->with_precondition->getPreconditions($env);
        $this->assertEquals([$this->precondition, $another], $pre);
    }


    public function testAchieve() : void
    {
        $env = $this->createMock(Setup\Environment::class);

        $this->objective
            ->expects($this->once())
            ->method("achieve")
            ->with($env)
            ->willReturn($env);

        $res = $this->with_precondition->achieve($env);
        $this->assertSame($env, $res);
    }
}
