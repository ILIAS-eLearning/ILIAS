<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

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
            ->setMethods(["getDataDirectoryPath", "scanDirectory", "isDirectory"])
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

    public function testGetDataDirectoryPath()
    {
        $base = dirname(__DIR__, 3);
        $this->assertEquals($base . "/data", $this->o->_getDataDirectoryPath());
    }
}
