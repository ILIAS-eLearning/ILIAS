<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */


use ILIAS\Setup;

class ilDatabaseConfigStoredObjective extends ilDatabaseObjective {
	public function getHash() : string {
		return hash("sha256", self::class);
	}

	public function getLabel() : string {
		return "Fill ini with settings for Services/Database";
	}

	public function isNotable() : bool {
		return false;
	}

	public function getPreconditions(Setup\Environment $environment) : array {
		$common_config = $environment->getConfigFor("common");
		return [
			new ilIniFilePopulatedObjective($common_config),
			new ilDatabaseExistsObjective($this->config)
		];
	}

	public function achieve(Setup\Environment $environment) : Setup\Environment {
		$client_ini = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI);

		$client_ini->setVariable("db", "type", $this->config->getType());
		$client_ini->setVariable("db", "host", $this->config->getHost());
		$client_ini->setVariable("db", "name", $this->config->getDatabase());
		$client_ini->setVariable("db", "user", $this->config->getUser());
		$client_ini->setVariable("db", "port", $this->config->getPort() ?? "");
		$pw = $this->config->getPassword();
		$client_ini->setVariable("db", "pass", $pw ? $pw->toString() : "");

		if (!$client_ini->write()) {
			throw new Setup\UnachievableException("Could not write client.ini.php");
		}

		return $environment;
	}
}
