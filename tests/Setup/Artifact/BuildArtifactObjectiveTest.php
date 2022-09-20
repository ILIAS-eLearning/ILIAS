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

namespace ILIAS\Tests\Setup\Artifact;

use ILIAS\Setup;
use ILIAS\Setup\Artifact;
use PHPUnit\Framework\TestCase;

class BuildArtifactObjectiveTest extends TestCase
{
    protected \PHPUnit\Framework\MockObject\MockObject $o;

    protected Artifact $artifact;
    protected Setup\Environment $env;

    public function setUp(): void
    {
        $this->o = $this
            ->getMockBuilder(Artifact\BuildArtifactObjective::class)
            ->onlyMethods(["build", "buildIn", "getArtifactPath"])
            ->getMock();

        $this->artifact = $this->createMock(Setup\Artifact::class);
        $this->env = $this->createMock(Setup\Environment::class);
    }

    public function testBuildInDefaultsToBuild(): void
    {
        $this->o = $this
            ->getMockBuilder(Artifact\BuildArtifactObjective::class)
            ->onlyMethods(["build", "getArtifactPath"])
            ->getMock();

        $this->o
            ->expects($this->once())
            ->method("build")
            ->with()
            ->willReturn($this->artifact);

        $this->assertSame($this->artifact, $this->o->buildIn($this->env));
    }

    public function testGetPreconditions(): void
    {
        $this->assertEquals([], $this->o->getPreconditions($this->env));
    }

    public function testGetHash(): void
    {
        $path = "path/to/artifact";

        $this->o
            ->expects($this->once())
            ->method("getArtifactPath")
            ->with()
            ->willReturn($path);

        $this->assertIsString($this->o->getHash());
    }

    public function testGetLabel(): void
    {
        $path = "path/to/artifact";

        $this->o
            ->expects($this->once())
            ->method("getArtifactPath")
            ->with()
            ->willReturn($path);

        $this->assertEquals("Build $path", $this->o->getLabel());
    }

    public function testIsNotable(): void
    {
        $this->assertTrue($this->o->isNotable());
    }

    public const TEST_PATH = "BuildArtifactObjectiveTest_testAchive";

    public function testAchieve(): void
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

    public function tearDown(): void
    {
        if (file_exists(getcwd() . "/" . self::TEST_PATH)) {
            unlink(getcwd() . "/" . self::TEST_PATH);
        }
    }
}
