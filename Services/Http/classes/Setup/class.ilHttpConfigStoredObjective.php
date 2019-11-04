<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilHttpConfigStoredObjective implements Setup\Objective {
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
		return "Store configuration of Services/Http";
	}

	public function isNotable() : bool {
		return false;
	}

	public function getPreconditions(Setup\Environment $environment) : array {
		$common_config = $environment->getConfigFor("common");
		return [
			new ilIniFilePopulatedObjective($common_config),
			new \ilSettingsFactoryExistsObjective()
		];
	}

	public function achieve(Setup\Environment $environment) : Setup\Environment {
		$ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);

		$ini->setVariable("server", "http_path", $this->config->getHttpPath());
		$ini->setVariable("https","auto_https_detect_enabled", $this->config->isAutodetectionEnabled() ? "1" : "0");
		$ini->setVariable("https","auto_https_detect_header_name", $this->config->getHeaderName());
		$ini->setVariable("https","auto_https_detect_header_value", $this->config->getHeaderValue());


		if (!$ini->write()) {
			throw new Setup\UnachievableException("Could not write ilias.ini.php");
		}

		$factory = $environment->getResource(Setup\Environment::RESOURCE_SETTINGS_FACTORY);
		$settings = $factory->settingsFor("common");
		$settings->set("proxy_status", (int) $this->config->isProxyEnabled());
		$settings->set("proxy_host", $this->config->getProxyHost());
		$settings->set("proxy_port", $this->config->getProxyPort());

		return $environment;
	}
}
