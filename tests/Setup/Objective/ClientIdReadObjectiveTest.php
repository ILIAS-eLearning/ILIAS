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
 
namespace ILIAS\Tests\Setup\Objective;

use ILIAS\Setup;
use ILIAS\Setup\Objective\ClientIdReadObjective;
use PHPUnit\Framework\TestCase;

class ClientIdReadObjectiveTest extends TestCase
{
    public function setUp() : void
    {
        $this->o = new class extends ClientIdReadObjective {
            public function _getDataDirectoryPath()
            {
                return $this->getDataDirectoryPath();
            }
        };
    }

    public function testGetHash() : void
    {
        $this->assertIsString($this->o->getHash());
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

    public function testAchieve() : void
    {
        $env = $this->createMock(Setup\Environment::class);

        $mock = $this->getMockBuilder(ClientIdReadObjective::class)
            ->onlyMethods(["getDataDirectoryPath", "scanDirectory", "isDirectory"])
            ->getMock();

        $DATA_DIR = "/foo/bar/data";
        $SOME_DIR = "clientid";
        $SOME_FILE = "some_file";
        $SCAN_RESULT = [".", "..", $SOME_DIR, $SOME_FILE];

        $mock
            ->expects($this->once())
            ->method("getDataDirectoryPath")
            ->willReturn($DATA_DIR);

        $mock
            ->expects($this->once())
            ->method("scanDirectory")
            ->with($DATA_DIR)
            ->willReturn($SCAN_RESULT);

        $mock
            ->expects($this->exactly(2))
            ->method("isDirectory")
            ->withConsecutive([$DATA_DIR . "/" . $SOME_DIR], [$DATA_DIR . "/" . $SOME_FILE])
            ->will($this->onConsecutiveCalls(true, false));

        $env
            ->expects($this->once())
            ->method("withResource")
            ->with(Setup\Environment::RESOURCE_CLIENT_ID, $SOME_DIR)
            ->willReturn($env);

        $res = $mock->achieve($env);
        $this->assertSame($env, $res);
    }

    public function testGetDataDirectoryPath() : void
    {
        $base = dirname(__DIR__, 3);
        $this->assertEquals($base . "/data", $this->o->_getDataDirectoryPath());
    }
}
