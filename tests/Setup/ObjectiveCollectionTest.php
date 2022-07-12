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
 
namespace ILIAS\Tests\Setup;

use ILIAS\Setup;
use PHPUnit\Framework\TestCase;

class ObjectiveCollectionTest extends TestCase
{
    use Helper;

    public function testGetObjectives() : void
    {
        $g1 = $this->newObjective();
        $g2 = $this->newObjective();
        $g3 = $this->newObjective();

        $c = new Setup\ObjectiveCollection("", false, $g1, $g2, $g3);

        $this->assertEquals([$g1, $g2, $g3], $c->getObjectives());
    }

    public function testGetHash() : void
    {
        $g1 = $this->newObjective();
        $g2 = $this->newObjective();
        $g3 = $this->newObjective();

        $c1 = new Setup\ObjectiveCollection("", false, $g1, $g2, $g3);
        $c2 = new Setup\ObjectiveCollection("", false, $g1, $g2);
        $c3 = new Setup\ObjectiveCollection("", false, $g1, $g2, $g3);

        $this->assertIsString($c1->getHash());
        $this->assertIsString($c2->getHash());
        $this->assertIsString($c3->getHash());

        $this->assertEquals($c1->getHash(), $c1->getHash());
        $this->assertNotEquals($c1->getHash(), $c2->getHash());
        $this->assertEquals($c1->getHash(), $c3->getHash());
    }

    public function testGetLabel() : void
    {
        $c = new Setup\ObjectiveCollection("LABEL", false);
        $this->assertEquals("LABEL", $c->getLabel());
    }

    public function testIsNotable() : void
    {
        $c1 = new Setup\ObjectiveCollection("", false);
        $c2 = new Setup\ObjectiveCollection("", true);
        $this->assertFalse($c1->isNotable());
        $this->assertTrue($c2->isNotable());
    }

    public function testGetPreconditions() : void
    {
        $g1 = $this->newObjective();
        $g2 = $this->newObjective();
        $g3 = $this->newObjective();

        $c = new Setup\ObjectiveCollection("", false, $g1, $g2, $g3);

        $env = $this->createMock(Setup\Environment::class);

        $pre = $c->getPreconditions($env);
        $this->assertEquals([$g1,$g2, $g3], $pre);
    }


    public function testAchieve() : void
    {
        $g1 = $this->newObjective();
        $g2 = $this->newObjective();
        $g3 = $this->newObjective();

        $c = new Setup\ObjectiveCollection("", false, $g1, $g2, $g3);

        $env = $this->createMock(Setup\Environment::class);

        foreach ([$g1,$g2,$g3] as $g) {
            $g
                ->expects($this->never())
                ->method("achieve");
        }

        $res = $c->achieve($env);
        $this->assertSame($env, $res);
    }
}
