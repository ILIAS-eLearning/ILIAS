<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilDatabaseServerIsConnectableObjective extends \ilDatabaseObjective {
	public function getHash() : string {
		return hash("sha256", implode("-", [
			self::class,
			$this->config->getHost(),
			$this->config->getPort(),
			$this->config->getUser(),
			$this->config->getPassword()->toString()
		]));
	}

	public function getLabel() : string {
		return "The database server is connectable with the supplied configuration.";
	}

	public function isNotable() : bool {
		return true;
	}

	public function getPreconditions(Setup\Environment $environment) : array {
		return [];
	}

	public function achieve(Setup\Environment $environment) : Setup\Environment {
		$db = \ilDBWrapperFactory::getWrapper($this->config->getType());
		$db->initFromIniFile($this->config->toMockIniFile());
		try {
			$connect = $db->connect();
		}
		catch (PDOException $e) {
			// 1049 is "unknown database", which is ok because we propably didn't
			// install the db yet,.
			if ($e->getCode() != 1049) {
				throw $e;
			}
			else {
				$connect = true;
			}
		}
		if (!$connect) {
			throw new \RuntimeException(
				"Database cannot be reached. Please check the credentials."
			);
		}
		return $environment;
	}
}
