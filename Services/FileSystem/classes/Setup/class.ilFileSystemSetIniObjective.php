<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */


use ILIAS\Setup;

class ilFileSystemSetIniObjective implements Setup\Objective {
	/**
	 * @var	\ilFileSystemSetupConfig
	 */
	protected $config;

	public function __construct(
		\ilFileSystemSetupConfig $config
	) {
		$this->config = $config;
	}

	public function getHash() : string {
		return hash("sha256", self::class);
	}

	public function getLabel() : string {
		return "Fill ini with settings for Services/FileSystem";
	}

	public function isNotable() : bool {
		return true;
	}

	public function getPreconditions(Setup\Environment $environment) : array {
		return [
			new ilIniFilePopulatedObjective() 
		];
	}

	public function achieve(Setup\Environment $environment) : Setup\Environment {
		$ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);

		$ini->setVariable("clients", "datadir", $this->config->getDataDir());

		if (!$ini->write()) {
			throw new \RuntimeException("Could not write ilias.ini.php");
		}

		return $environment;
	}
}
