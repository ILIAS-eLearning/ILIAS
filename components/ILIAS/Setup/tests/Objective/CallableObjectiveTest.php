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
use ILIAS\Tests\Setup as Test;
use PHPUnit\Framework\TestCase;

class CallableObjectiveTest extends TestCase
{
    use Test\Helper;

    protected ?Setup\Environment $env;
    protected Setup\Objective $p;
    protected Objective\CallableObjective $o;

    public function myMethod(Setup\Environment $environment): Setup\Environment
    {
        $this->env = $environment;
        return $environment;
    }

    public const NAME = "CALL MY METHOD!";

    public function setUp(): void
    {
        $this->p = $this->newObjective();

        $this->o = new Objective\CallableObjective(
            [$this, "myMethod"],
            self::NAME,
            false,
            $this->p
        );
    }

    public function testGetHash(): void
    {
        $this->assertIsString($this->o->getHash());
    }

    public function testGetLabel(): void
    {
        $this->assertEquals(self::NAME, $this->o->getLabel());
    }

    public function testIsNotable(): void
    {
        $this->assertFalse($this->o->isNotable());
    }

    public function testGetPreconditions(): void
    {
        $env = $this->createMock(Setup\Environment::class);

        $pre = $this->o->getPreconditions($env);
        $this->assertEquals([$this->p], $pre);
    }


    public function testAchieve(): void
    {
        $this->env = null;

        $env = $this->createMock(Setup\Environment::class);

        $res = $this->o->achieve($env);
        $this->assertSame($env, $res);
        $this->assertSame($this->env, $env);
    }
}
