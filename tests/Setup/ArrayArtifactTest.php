<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup;

use ILIAS\Setup;

class ArrayArtifactTest extends \PHPUnit\Framework\TestCase {
	public function testSerialize() {
		$data = [
			"one" => 1,
			"two" => 2,
			"nested" => [
				"array" => "are nice"
			]
		];

		$a = new Setup\ArrayArtifact($data);

		$serialized = $a->serialize();

		$this->assertEquals($data, eval("?>".$serialized));
	}

	public function testOnlyPrimitives() {
		$this->expectException(\InvalidArgumentException::class);

		$data = [ $this ];

		$a = new Setup\ArrayArtifact($data);
	}
}
