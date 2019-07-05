<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup\CLI;

use ILIAS\Setup;

class ObjectiveIteratorTest extends \PHPUnit\Framework\TestCase {
	public function testBasicAlgorithm() {
		$hash = "my hash";
		$objective = $this->newObjective($hash);
		$environment = $this->createMock(Setup\Environment::class);

		$objective
			->expects($this->once())
			->method("getPreconditions")
			->with($environment)
			->willReturn([]);

		$iterator = new Setup\ObjectiveIterator($environment, $objective);

		$this->assertTrue($iterator->valid());
		$this->assertSame($objective, $iterator->current());
		$this->assertSame($hash, $iterator->key());

		$iterator->next();

		$this->assertFalse($iterator->valid());
	}

	public function testRewind() {
		$hash = "my hash";
		$objective = $this->newObjective($hash);
		$environment = $this->createMock(Setup\Environment::class);

		$iterator = new Setup\ObjectiveIterator($environment, $objective);

		$objective
			->expects($this->once())
			->method("getPreconditions")
			->with($environment)
			->willReturn([]);

		$iterator->next();
		$iterator->rewind();

		$this->assertTrue($iterator->valid());
		$this->assertSame($objective, $iterator->current());
		$this->assertSame($hash, $iterator->key());
	}

	public function testAllObjectives() {
		$environment = $this->createMock(Setup\Environment::class);

		$objective1 = $this->newObjective();
		$objective11 = $this->newObjective();
		$objective12 = $this->newObjective();
		$objective121 = $this->newObjective();

		$objective1
			->method("getPreconditions")
			->with($environment)
			->willReturn([$objective11, $objective12]);

		$objective11
			->method("getPreconditions")
			->with($environment)
			->willReturn([]);

		$objective12
			->method("getPreconditions")
			->with($environment)
			->willReturn([$objective121]);

		$objective121
			->method("getPreconditions")
			->with($environment)
			->willReturn([]);

		$iterator = new Setup\ObjectiveIterator($environment, $objective1);

		$expected = [
			$objective11->getHash() => $objective11,
			$objective121->getHash() => $objective121,
			$objective12->getHash() => $objective12,
			$objective1->getHash() => $objective1
		];

		$this->assertEquals($expected, iterator_to_array($iterator));
	}

	public function testAllObjectivesOnlyReturnsObjectiveOnce() {
		$environment = $this->createMock(Setup\Environment::class);

		$objective1 = $this->newObjective();
		$objective11 = $this->newObjective();

		$objective1
			->method("getPreconditions")
			->with($environment)
			->willReturn([$objective11, $objective11]);

		$objective11
			->method("getPreconditions")
			->with($environment)
			->willReturn([]);

		$iterator = new Setup\ObjectiveIterator($environment, $objective1);

		$expected = [
			$objective11->getHash() => $objective11,
			$objective1->getHash() => $objective1
		];
		$this->assertEquals($expected, iterator_to_array($iterator));
	}

	public function testAllObjectivesDetectsCycle() {
		$environment = $this->createMock(Setup\Environment::class);

		$objective1 = $this->newObjective();
		$objective2 = $this->newObjective();

		$objective1
			->method("getPreconditions")
			->with($environment)
			->willReturn([$objective2]);

		$objective2
			->method("getPreconditions")
			->with($environment)
			->willReturn([$objective1]);

		$this->expectException(Setup\UnachievableException::class);		

		$iterator = new Setup\ObjectiveIterator($environment, $objective1);
		iterator_to_array($iterator);
	}

	public function testSetEnvironment() {
		$env1 = new Setup\ArrayEnvironment([]);
		$env2 = new Setup\ArrayEnvironment([]);

		$objective1 = $this->newObjective();
		$objective2 = $this->newObjective();

		$objective1
			->expects($this->atLeastOnce())
			->method("getPreconditions")
			->with($env1)
			->willReturn([$objective2]);

		$objective2
			->expects($this->atLeastOnce())
			->method("getPreconditions")
			->with($env2)
			->willReturn([]);

		$iterator = new Setup\ObjectiveIterator($env1, $objective1);

		$iterator->setEnvironment($env2);
		$iterator->next();
	}

	protected function newObjective($hash = null) {
		static $no = 0;

		$objective = $this
			->getMockBuilder(Setup\Objective::class)
			->setMethods(["getHash", "getLabel", "isNotable", "withResourcesFrom", "getPreconditions", "achieve"])
			->setMockClassName("Mock_ObjectiveNo".($no++))
			->getMock();

		$objective
			->method("getHash")
			->willReturn($hash ?? "".$no);

		return $objective;
	}
}
