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

class NullObjectiveTest extends TestCase
{
    protected Objective\NullObjective $o;

    public function setUp(): void
    {
        $this->o = new Objective\NullObjective();
    }

    public function testGetHash(): void
    {
        $this->assertIsString($this->o->getHash());
    }

    public function testGetLabel(): void
    {
        $this->assertEquals("Nothing to do.", $this->o->getLabel());
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

    public function testAchieve(): void
    {
        $env = $this->createMock(Setup\Environment::class);

        $res = $this->o->achieve($env);
        $this->assertSame($env, $res);
    }
}
