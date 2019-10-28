<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilLanguageConfigStoredObjective implements Setup\Objective {
	/**
	 * @var	\ilLanguageSetupConfig
	 */
	protected $config;

	public function __construct(
		\ilLanguageSetupConfig $config
	) {
		$this->config = $config;
	}

	public function getHash() : string {
		return hash("sha256", self::class);
	}

	public function getLabel() : string {
		return "Fill ini with settings for Services/Language";
	}

	public function isNotable() : bool {
		return false;
	}

	public function getPreconditions(Setup\Environment $environment) : array {
		$common_config = $environment->getConfigFor("common");
		return [
			new ilIniFilePopulatedObjective($common_config)
		];
	}

	public function achieve(Setup\Environment $environment) : Setup\Environment {
		$client_ini = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI);

		$client_ini->setVariable("language","default", $this->config->getDefaultLanguage());

		if (!$client_ini->write()) {
			throw new Setup\UnachievableException("Could not write client.ini.php");
		}

		return $environment;
	}
}
