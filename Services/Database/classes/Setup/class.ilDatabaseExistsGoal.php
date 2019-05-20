<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilDatabaseExistsGoal extends \ilDatabaseGoal {
	public function getHash() : string {
		return hash("sha256", implode("-", [
			self::class,
			$this->config->getHost(),
			$this->config->getPort(),
			$this->config->getDatabase()
		]));
	}

	public function getLabel() : string {
		return "The database exists on the server.";
	}

	public function isNotable() : bool {
		return true;
	}

	public function getPreconditions(Setup\Environment $environment) : array {
		$preconditions = [
			new \ilDatabaseServerIsConnectableGoal($this->config)
		];
		if ($this->config->getCreateDatabase()) {
			$preconditions[] = new \ilDatabaseCreatedGoal($this->config);
		}
		return $preconditions;
	}

	public function achieve(Setup\Environment $environment) : Setup\Environment {
		$db = \ilDBWrapperFactory::getWrapper($this->config->getType());
		$db->initFromIniFile($this->config->toMockIniFile());
		$connect = $db->connect(true);
		if (!$connect) {
			throw new \RuntimeException(
				"Database cannot be connected. Please check the credentials."
			);
		}
		return $environment->withResource(Setup\Environment::RESOURCE_DATABASE, $db);
	}
}
