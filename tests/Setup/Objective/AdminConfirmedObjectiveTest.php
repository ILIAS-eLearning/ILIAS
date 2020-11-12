<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup\Objective;

use ILIAS\Setup;
use ILIAS\Setup\Objective;
use PHPUnit\Framework\TestCase;

class AdminConfirmedObjectiveTest extends TestCase
{
    /**
     * @var string
     */
    protected $message;

    /**
     * @var Objective\AdminConfirmedObjective
     */
    protected $o;

    public function setUp() : void
    {
        $this->message = "This needs to be confirmed...";
        $this->o = new Objective\AdminConfirmedObjective($this->message);
    }

    public function testGetHash() : void
    {
        $this->assertIsString($this->o->getHash());
    }

    public function testHashIsDifferentForDifferentMessages() : void
    {
        $other = new Objective\AdminConfirmedObjective("");
        $this->assertNotEquals($this->o->getHash(), $other->getHash());
    }

    public function testGetLabel() : void
    {
        $this->assertIsString($this->o->getLabel());
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

    public function testAchieveWithConfirmation() : void
    {
        $env = $this->createMock(Setup\Environment::class);
        $admin_interaction = $this->createMock(Setup\AdminInteraction::class);

        $env
            ->method("getResource")
            ->will($this->returnValueMap([
                [Setup\Environment::RESOURCE_ADMIN_INTERACTION, $admin_interaction]
            ]));

        $admin_interaction
            ->expects($this->once())
            ->method("confirmOrDeny")
            ->with($this->message)
            ->willReturn(true);

        $res = $this->o->achieve($env);
        $this->assertSame($env, $res);
    }

    public function testAchieveWithDenial() : void
    {
        $this->expectException(Setup\NoConfirmationException::class);

        $env = $this->createMock(Setup\Environment::class);
        $admin_interaction = $this->createMock(Setup\AdminInteraction::class);

        $env
            ->method("getResource")
            ->will($this->returnValueMap([
                [Setup\Environment::RESOURCE_ADMIN_INTERACTION, $admin_interaction]
            ]));

        $admin_interaction
            ->expects($this->once())
            ->method("confirmOrDeny")
            ->with($this->message)
            ->willReturn(false);

        $this->o->achieve($env);
    }
}
