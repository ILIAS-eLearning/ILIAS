<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup\Objective;

use ILIAS\Setup;
use ILIAS\Setup\Objective;
use PHPUnit\Framework\TestCase;

class NullObjectiveTest extends TestCase
{
    /**
     * @var Objective\NullObjective
     */
    protected $o;

    public function setUp() : void
    {
        $this->o = new Objective\NullObjective();
    }

    public function testGetHash() : void
    {
        $this->assertIsString($this->o->getHash());
    }

    public function testGetLabel() : void
    {
        $this->assertEquals("Nothing to do.", $this->o->getLabel());
    }

    public function testIsNotable() : void
    {
        $this->assertFalse($this->o->isNotable());
    }

    public function testGetPreconditions() : void
    {
        $env = $this->createMock(Setup\Environment::class);

        $pre = $this->o->getPreconditions($env);
        $this->assertEquals([], $pre);
    }

    public function testAchieve() : void
    {
        $env = $this->createMock(Setup\Environment::class);

        $res = $this->o->achieve($env);
        $this->assertSame($env, $res);
    }
}
