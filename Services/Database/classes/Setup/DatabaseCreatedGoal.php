<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class DatabaseCreatedGoal extends DatabaseGoal {
	public function getHash() : string {
		return hash("sha256", implode("-", [
			self::class,
			$this->config->getHost(),
			$this->config->getPort(),
			$this->config->getDatabase()
		]));
	}

	public function getLabel() : string {
		return "The database is created on the server.";
	}

	public function isNotable() : bool {
		return true;
	}

	public function getPreconditions() : array {
		return [
			new \DatabaseServerIsConnectableGoal($this->config)
		];
	}

	public function achieve(Setup\Environment $environment) : Setup\Environment {
		$c = $this->config;
		$db = ilDBWrapperFactory::getWrapper($this->config->getType());
		$db->initFromIniFile($c->toMockIniFile());

		$connect = $db->connect(true);
		if ($connect) {
			// Database seems to exist already.
			return $environment;
		}

		if (!$db->createDatabase($c->getDatabase(), "utf8", $c->getCollation())) {
			throw new \RuntimeException(
				"Database cannot be created."
			);
		}

		return $environment;
	}
}
