<?php

declare(strict_types=1);

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

namespace ILIAS\Tests\Setup\Objective;

use ILIAS\Setup;
use ILIAS\Setup\Objective;
use PHPUnit\Framework\TestCase;

class AdminConfirmedObjectiveTest extends TestCase
{
    protected string $message;
    protected Objective\AdminConfirmedObjective $o;

    public function setUp(): void
    {
        $this->message = "This needs to be confirmed...";
        $this->o = new Objective\AdminConfirmedObjective($this->message);
    }

    public function testGetHash(): void
    {
        $this->assertIsString($this->o->getHash());
    }

    public function testHashIsDifferentForDifferentMessages(): void
    {
        $other = new Objective\AdminConfirmedObjective("");
        $this->assertNotEquals($this->o->getHash(), $other->getHash());
    }

    public function testGetLabel(): void
    {
        $this->assertIsString($this->o->getLabel());
    }

    public function testIsNotable(): void
    {
        $this->assertFalse($this->o->isNotable());
    }

    public function testGetPreconditions(): void
    {
        $env = $this->createMock(Setup\Environment::class);

        $pre = $this->o->getPreconditions($env);
        $this->assertEquals([], $pre);
    }

    public function testAchieveWithConfirmation(): void
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

    public function testAchieveWithDenial(): void
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
