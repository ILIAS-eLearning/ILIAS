<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class DatabaseServerIsConnectableGoal implements Setup\Goal {
	/**
	 * @var	DatabaseSetupConfig
	 */
	protected $config;

	public function __construct(\DatabaseSetupConfig $config) {
		$this->config = $config;
	}

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

	public function withResourcesFrom(Setup\Environment $environment) : Setup\Goal {
		return $this;
	}

	public function getPreconditions() : array {
		return [];
	}

	public function achieve(Setup\Environment $environment) {
		$db = ilDBWrapperFactory::getWrapper($this->config->getType());
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
			throw new \LogicException(
				"Database cannot be reached. Please check the credentials."
			);
		}
	}
}
