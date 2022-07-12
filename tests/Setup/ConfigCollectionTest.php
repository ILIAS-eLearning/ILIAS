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

class ConfigCollectionTest extends TestCase
{
    use Helper;

    public function testConstruct() : void
    {
        $c1 = $this->newConfig();
        $c2 = $this->newConfig();
        $c3 = $this->newConfig();

        $c = new Setup\ConfigCollection(["c1" => $c1, "c2" => $c2, "c3" => $c3]);

        $this->assertInstanceOf(Setup\Config::class, $c);
    }

    public function testGetConfig() : void
    {
        $c1 = $this->newConfig();
        $c2 = $this->newConfig();
        $c3 = $this->newConfig();

        $c = new Setup\ConfigCollection(["c1" => $c1, "c2" => $c2, "c3" => $c3]);

        $this->assertEquals($c1, $c->getConfig("c1"));
        $this->assertEquals($c2, $c->getConfig("c2"));
        $this->assertEquals($c3, $c->getConfig("c3"));
    }

    public function testGetKeys() : void
    {
        $c1 = $this->newConfig();
        $c2 = $this->newConfig();
        $c3 = $this->newConfig();

        $c = new Setup\ConfigCollection(["c1" => $c1, "c2" => $c2, "c3" => $c3]);

        $this->assertEquals(["c1", "c2", "c3"], $c->getKeys());
    }

    public function testMaybeGetConfig() : void
    {
        $c1 = $this->newConfig();
        $c2 = $this->newConfig();
        $c3 = $this->newConfig();

        $c = new Setup\ConfigCollection(["c1" => $c1, "c2" => $c2]);

        $this->assertEquals($c1, $c->maybeGetConfig("c1"));
        $this->assertEquals($c2, $c->maybeGetConfig("c2"));
        $this->assertEquals(null, $c->maybeGetConfig("c3"));
    }
}
