<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup\Artifact;

use ILIAS\Setup;
use ILIAS\Setup\Artifact;
use PHPUnit\Framework\TestCase;

class BuildArtifactObjectiveTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $o;

    /**
     * @var Artifact|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $artifact;

    /**
     * @var Setup\Environment|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $env;

    public function setUp() : void
    {
        $this->o = $this
            ->getMockBuilder(Artifact\BuildArtifactObjective::class)
            ->setMethods(["build", "buildIn", "getArtifactPath"])
            ->getMock();

        $this->artifact = $this->createMock(Setup\Artifact::class);
        $this->env = $this->createMock(Setup\Environment::class);
    }

    public function testBuildInDefaultsToBuild() : void
    {
        $this->o = $this
            ->getMockBuilder(Artifact\BuildArtifactObjective::class)
            ->setMethods(["build", "getArtifactPath"])
            ->getMock();

        $this->o
            ->expects($this->once())
            ->method("build")
            ->with()
            ->willReturn($this->artifact);

        $this->assertSame($this->artifact, $this->o->buildIn($this->env));
    }

    public function testGetPreconditions() : void
    {
        $this->assertEquals([], $this->o->getPreconditions($this->env));
    }

    public function testGetHash() : void
    {
        $path = "path/to/artifact";

        $this->o
            ->expects($this->once())
            ->method("getArtifactPath")
            ->with()
            ->willReturn($path);

        $this->assertIsString($this->o->getHash());
    }

    public function testGetLabel() : void
    {
        $path = "path/to/artifact";

        $this->o
            ->expects($this->once())
            ->method("getArtifactPath")
            ->with()
            ->willReturn($path);

        $this->assertEquals("Build $path", $this->o->getLabel());
    }

    public function testIsNotable() : void
    {
        $this->assertTrue($this->o->isNotable());
    }

    const TEST_PATH = "BuildArtifactObjectiveTest_testAchive";

    public function testAchieve() : void
    {
        $path = self::TEST_PATH;
        $this->o
            ->expects($this->atLeastOnce())
            ->method("getArtifactPath")
            ->with()
            ->willReturn($path);

        $this->o
            ->expects($this->once())
            ->method("buildIn")
            ->with($this->env)
            ->willReturn($this->artifact);

        $artifact = "THIS IS THE ARTIFACT";
        $this->artifact
            ->expects($this->once())
            ->method("serialize")
            ->with()
            ->willReturn($artifact);

        $this->o->achieve($this->env);

        $this->assertEquals($artifact, file_get_contents($path));
    }

    public function tearDown() : void
    {
        if (file_exists(getcwd() . "/" . self::TEST_PATH)) {
            unlink(getcwd() . "/" . self::TEST_PATH);
        }
    }
}
