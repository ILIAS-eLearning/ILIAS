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

class TentativelyTest extends TestCase
{
    use Test\Helper;

    protected Setup\Objective $objective;
    protected Setup\Objective $precondition;
    protected Objective\Tentatively $tentatively;

    public function setUp(): void
    {
        $this->objective = $this->newObjective();
        $this->precondition = $this->newObjective();

        $this->tentatively = new Objective\Tentatively($this->objective);
        $this->double_tentatively = new Objective\Tentatively($this->tentatively);
    }

    public function testGetHash(): void
    {
        $this->assertEquals(
            "tentatively " . $this->objective->getHash(),
            $this->tentatively->getHash()
        );
    }

    public function testDoubleTentativelyGetHash(): void
    {
        $this->assertEquals(
            $this->tentatively->getHash(),
            $this->double_tentatively->getHash()
        );
    }

    public function testGetLabel(): void
    {
        $label = "some_label";

        $this->objective
            ->expects($this->once())
            ->method("getLabel")
            ->willReturn($label);

        $this->assertEquals(
            "Tentatively: $label",
            $this->tentatively->getLabel()
        );
    }

    public function testDoubleTentativelyGetLabel(): void
    {
        $label = "some_label";

        $this->objective
            ->method("getLabel")
            ->willReturn($label);

        $this->assertEquals(
            $this->tentatively->getLabel(),
            $this->double_tentatively->getLabel()
        );
    }
    public function testIsNotable(): void
    {
        $notable = true;

        $this->objective
            ->method("isNotable")
            ->willReturn($notable);

        $this->assertEquals($notable, $this->tentatively->isNotable());
        $this->assertEquals($notable, $this->double_tentatively->isNotable());
    }

    public function testGetPreconditions(): void
    {
        $other = $this->newObjective();

        $env = $this->createMock(Setup\Environment::class);

        $this->objective
            ->expects($this->once())
            ->method("getPreconditions")
            ->with($env)
            ->willReturn([$other]);

        $this->assertEquals(
            [new Objective\Tentatively($other)],
            $this->tentatively->getPreconditions($env)
        );
    }

    public function testAchieve(): void
    {
        $env = $this->createMock(Setup\Environment::class);

        $this->objective
            ->expects($this->once())
            ->method("achieve")
            ->with($env)
            ->willReturn($env);

        $res = $this->tentatively->achieve($env);
        $this->assertSame($env, $res);
    }

    public function testAchieveThrows(): void
    {
        $env = $this->createMock(Setup\Environment::class);

        $this->objective
            ->expects($this->once())
            ->method("achieve")
            ->with($env)
            ->will($this->throwException(new Setup\UnachievableException()));

        $res = $this->tentatively->achieve($env);
        $this->assertSame($env, $res);
    }

    public function testIsApplicable(): void
    {
        $env = $this->createMock(Setup\Environment::class);
        $is_applicable = random_int(0, 1) == 1;

        $this->objective
            ->expects($this->once())
            ->method("isApplicable")
            ->with($env)
            ->willReturn($is_applicable);

        $this->assertEquals($is_applicable, $this->tentatively->isApplicable($env));
    }
}
