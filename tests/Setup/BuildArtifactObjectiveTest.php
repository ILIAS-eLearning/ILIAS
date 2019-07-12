<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup;

use ILIAS\Setup;

class BuildArtifactObjectiveTest extends \PHPUnit\Framework\TestCase {
	public function setUp() : void {
		$this->o = $this
			->getMockBuilder(Setup\BuildArtifactObjective::class)
			->setMethods(["build", "buildIn", "getArtifactPath"])
			->getMock();

		$this->artifact = $this->createMock(Setup\Artifact::class);
		$this->env = $this->createMock(Setup\Environment::class);
	}

	public function testBuildInDefaultsToBuild() {
		$this->o = $this
			->getMockBuilder(Setup\BuildArtifactObjective::class)
			->setMethods(["build", "getArtifactPath"])
			->getMock();

		$this->o
			->expects($this->once())
			->method("build")
			->with()
			->willReturn($this->artifact);

		$this->assertSame($this->artifact, $this->o->buildIn($this->env));
	}

	public function testGetPreconditions() {
		$this->assertEquals([], $this->o->getPreconditions($this->env));
	}

	public function testGetHash() {
		$path = "path/to/artifact";

		$this->o
			->expects($this->once())
			->method("getArtifactPath")
			->with()
			->willReturn($path);

		$this->assertIsString($this->o->getHash());
	}

	public function testGetLabel() {
		$path = "path/to/artifact";

		$this->o
			->expects($this->once())
			->method("getArtifactPath")
			->with()
			->willReturn($path);

		$this->assertEquals("Build $path", $this->o->getLabel());
	}

	public function testIsNotable() {
		$this->assertTrue($this->o->isNotable());
	}

	const TEST_PATH = "BuildArtifactObjectiveTest_testAchive";

	public function testAchieve() {
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

	public function tearDown() : void {
		if (file_exists(getcwd()."/".self::TEST_PATH)) {
			unlink(getcwd()."/".self::TEST_PATH);
		}
	}
}
