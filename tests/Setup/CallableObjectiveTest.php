<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup;

require_once(__DIR__ . "/Helper.php");

use ILIAS\Setup;

class CallableObjectiveTest extends \PHPUnit\Framework\TestCase
{
    use Helper;

    public function myMethod(Setup\Environment $environment)
    {
        $this->env = $environment;
        return $environment;
    }

    const NAME = "CALL MY METHOD!";

    public function setUp() : void
    {
        $this->p = $this->newObjective();

        $this->o = new Setup\CallableObjective(
            [$this, "myMethod"],
            self::NAME,
            false,
            $this->p
        );
    }

    public function testGetHash()
    {
        $this->assertIsString($this->o->getHash());
    }

    public function testGetLabel()
    {
        $this->assertEquals(self::NAME, $this->o->getLabel());
    }

    public function testIsNotable()
    {
        $this->assertFalse($this->o->isNotable());
    }

    public function testGetPreconditions()
    {
        $env = $this->createMock(Setup\Environment::class);

        $pre = $this->o->getPreconditions($env);
        $this->assertEquals([$this->p], $pre);
    }


    public function testAchieve()
    {
        $this->env = null;

        $env = $this->createMock(Setup\Environment::class);

        $res = $this->o->achieve($env);
        $this->assertSame($env, $res);
        $this->assertSame($this->env, $env);
    }
}
