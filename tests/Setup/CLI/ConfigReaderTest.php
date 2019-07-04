<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup\CLI;

use ILIAS\Setup;
use Symfony\Component\Console\Tester\CommandTester;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;

class ConfigReaderTest extends \PHPUnit\Framework\TestCase {
	public function testReadConfigFile() {
		$filename = tempnam("/tmp", "ILIAS");
		$expected = [
			"some" => [
				"nested" => "config"
			]
		];
		file_put_contents($filename, json_encode($expected));

		$obj = new Setup\CLI\ConfigReader();

		$config = $obj->readConfigFile($filename);

		$this->assertEquals($expected, $config);
	}
}
