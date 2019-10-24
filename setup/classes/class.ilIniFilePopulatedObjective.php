<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilIniFilePopulatedObjective implements Setup\Objective {
	public function getHash() : string {
		return hash("sha256", self::class);
	}

	public function getLabel() : string {
		return "The ilias.ini.php is populated with defaults.";
	}

	public function isNotable() : bool {
		return true;
	}

	public function getPreconditions(Setup\Environment $environment) : array {
		return [
			new Setup\CanCreateFilesInDirectoryCondition(dirname(__DIR__, 2)),
		];
	}

	public function achieve(Setup\Environment $environment) : Setup\Environment {
		$path = dirname(__DIR__, 2)."/ilias.ini.php";
		$ini = new ilIniFile($path);
		$ini->GROUPS = parse_ini_file(__DIR__."/../ilias.master.ini.php",true);
		$ini->write();

		return $environment
			->withResource(Setup\Environment::RESOURCE_ILIAS_INI, $ini);
	}
}
