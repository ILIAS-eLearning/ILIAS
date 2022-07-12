<?php declare(strict_types=1);

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
 
namespace ILIAS\Tests\Setup\Condition;

use ILIAS\Setup;
use ILIAS\Setup\Condition;
use PHPUnit\Framework\TestCase;

class ExternalConditionObjectiveTest extends TestCase
{
    protected string $label_t;
    protected Condition\ExternalConditionObjective $t;
    protected string $label_f;
    protected Condition\ExternalConditionObjective $f;

    public function setUp() : void
    {
        $this->label_t = "condition_true";
        $this->t = new Condition\ExternalConditionObjective(
            $this->label_t,
            function (Setup\Environment $e) {
                return true;
            }
        );
        $this->label_f = "condition_false";
        $this->f = new Condition\ExternalConditionObjective(
            $this->label_f,
            function (Setup\Environment $e) {
                return false;
            }
        );
    }

    public function testGetHash() : void
    {
        $this->assertIsString($this->t->getHash());
    }

    public function testHashIsDifferentForDifferentMessages() : void
    {
        $this->assertNotEquals($this->t->getHash(), $this->f->getHash());
    }

    public function testGetLabel() : void
    {
        $this->assertIsString($this->f->getLabel());
        $this->assertEquals($this->label_f, $this->f->getLabel());
        $this->assertEquals($this->label_t, $this->t->getLabel());
    }

    public function testIsNotable() : void
    {
        $this->assertTrue($this->f->isNotable());
    }

    public function testGetPreconditions() : void
    {
        $env = $this->createMock(Setup\Environment::class);

        $pre = $this->f->getPreconditions($env);
        $this->assertEquals([], $pre);
    }

    public function testAchieveFalse() : void
    {
        $this->expectException(Setup\UnachievableException::class);
        $env = $this->createMock(Setup\Environment::class);
        $this->f->achieve($env);
    }


    public function testAchieveTrue() : void
    {
        $env = $this->createMock(Setup\Environment::class);
        $res = $this->t->achieve($env);
        $this->assertEquals($env, $res);
    }
}
