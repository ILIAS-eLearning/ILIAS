<?php
/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\CLI;

use ILIAS\Setup\Agent;
use ILIAS\Setup\ArrayEnvironment;
use ILIAS\Setup\ObjectiveIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Read a json-formatted config from a file.
 */
class ConfigReader {
	/**
	 * TODO: We could use the "give me a transformation and I'll give you your
	 *       result" pattern from the input paper here.
	 */
	public function readConfigFile(string $name) : array {
		if (!file_exists($name) || !is_readable($name)) {
			throw new \InvalidArgumentException(
				"Config-file $name does not exist or is not readable."
			);
		}
		$json = json_decode(file_get_contents($name), JSON_OBJECT_AS_ARRAY);
		if (!is_array($json)) {
			throw new \InvalidArgumentException(
				"Could not find JSON-array in $name."
			);
		}
		return $json;
	}
}
