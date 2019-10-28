<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilHttpSetIniObjective implements Setup\Objective {
	/**
	 * @var	\ilHttpSetupConfig
	 */
	protected $config;

	public function __construct(
		\ilHttpSetupConfig $config
	) {
		$this->config = $config;
	}

	public function getHash() : string {
		return hash("sha256", self::class);
	}

	public function getLabel() : string {
		return "Fill ini with settings for Services/Http";
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
		$ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);

		$ini->setVariable("server", "http_path", $this->config->getHttpPath());
		$ini->setVariable("https","auto_https_detect_enabled", $this->config->isAutodetectionEnabled() ? "1" : "0");
		$ini->setVariable("https","auto_https_detect_header_name", $this->config->getHeaderName());
		$ini->setVariable("https","auto_https_detect_header_value", $this->config->getHeaderValue());

		if (!$ini->write()) {
			throw new \RuntimeException("Could not write ilias.ini.php");
		}

		return $environment;
	}
}
