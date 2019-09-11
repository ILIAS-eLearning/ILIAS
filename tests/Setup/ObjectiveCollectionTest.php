<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup;

require_once(__DIR__."/Helper.php");

use ILIAS\Setup;

class ObjectiveCollectionTest extends \PHPUnit\Framework\TestCase {
	use Helper;

	public function testGetObjectives() {
		$g1 = $this->newObjective();
		$g2 = $this->newObjective();
		$g3 = $this->newObjective();

		$c = new Setup\ObjectiveCollection("", false, $g1, $g2, $g3);

		$this->assertEquals([$g1, $g2, $g3], $c->getObjectives());
	}

	public function testGetHash() {
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

	public function testGetLabel() {
		$c = new Setup\ObjectiveCollection("LABEL", false);
		$this->assertEquals("LABEL", $c->getLabel());
	}

	public function testIsNotable() {
		$c1 = new Setup\ObjectiveCollection("", false);
		$c2 = new Setup\ObjectiveCollection("", true);
		$this->assertFalse($c1->isNotable());
		$this->assertTrue($c2->isNotable());
	}

	public function testGetPreconditions() {
		$g1 = $this->newObjective();
		$g2 = $this->newObjective();
		$g3 = $this->newObjective();

		$c = new Setup\ObjectiveCollection("", false, $g1, $g2, $g3);

		$env = $this->createMock(Setup\Environment::class);

		$pre = $c->getPreconditions($env);
		$this->assertEquals([$g1,$g2, $g3], $pre);	
	}


	public function testAchieve() {
		$g1 = $this->newObjective();
		$g2 = $this->newObjective();
		$g3 = $this->newObjective();

		$c = new Setup\ObjectiveCollection("", false, $g1, $g2, $g3);

		$env = $this->createMock(Setup\Environment::class);

		foreach([$g1,$g2,$g3] as $g) {
			$g
				->expects($this->never())
				->method("achieve");
		}

		$res = $c->achieve($env);
		$this->assertSame($env, $res);
	}
}
