<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup\Objective;

use ILIAS\Setup;
use ILIAS\Setup\Objective;
use ILIAS\Tests\Setup as Test;
use PHPUnit\Framework\TestCase;

class CallableObjectiveTest extends TestCase
{
    use Test\Helper;

    /**
     * @var Setup\Environment
     */
    protected $env;

    /**
     * @var Setup\Objective
     */
    protected $p;

    /**
     * @var Objective\CallableObjective
     */
    protected $o;

    public function myMethod(Setup\Environment $environment) : Setup\Environment
    {
        $this->env = $environment;
        return $environment;
    }

    const NAME = "CALL MY METHOD!";

    public function setUp() : void
    {
        $this->p = $this->newObjective();

        $this->o = new Objective\CallableObjective(
            [$this, "myMethod"],
            self::NAME,
            false,
            $this->p
        );
    }

    public function testGetHash() : void
    {
        $this->assertIsString($this->o->getHash());
    }

    public function testGetLabel() : void
    {
        $this->assertEquals(self::NAME, $this->o->getLabel());
    }

    public function testIsNotable() : void
    {
        $this->assertFalse($this->o->isNotable());
    }

    public function testGetPreconditions() : void
    {
        $env = $this->createMock(Setup\Environment::class);

        $pre = $this->o->getPreconditions($env);
        $this->assertEquals([$this->p], $pre);
    }


    public function testAchieve() : void
    {
        $this->env = null;

        $env = $this->createMock(Setup\Environment::class);

        $res = $this->o->achieve($env);
        $this->assertSame($env, $res);
        $this->assertSame($this->env, $env);
    }
}
