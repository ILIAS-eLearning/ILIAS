<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;
use ILIAS\Data\Factory as DataFactory;

class DatabaseSetupConsumer implements Setup\Consumer {
	/**
	 * @var DataFactory
	 */
	protected $data_factory;

	public function __construct(DataFactory $data_factory) {
		$this->data_factory = $data_factory;
	}

	/**
	 * @inheritdocs
	 */
	public function hasConfig() : bool {
		return true;
	}

	/**
	 * @inheritdocs
	 */
	public function getConfigInput(Setup\Config $config = null) : ILIAS\UI\Component\Input\Field\Input {
		throw new \LogicException("NYI!");
	}

	/**
	 * @inheritdocs
	 *
	 * TODO: Use \DatabaseSetupConfig as return type once variance is implemented
	 * in PHP.
	 */
	public function getConfigFromArray(array $data) : Setup\Config {
		return new \DatabaseSetupConfig(
			$data["type"] ?? null,
			$data["host"] ?? null,
			$data["database"] ?? null,
			$data["user"] ?? null,
			$data["password"] ? $this->data_factory->password($data["password"]) : null,
			$data["create_database"] ?? null,
			$data["port"] ?? null
		);
	}

	/**
	 * @inheritdocs
	 */
	public function getInstallGoal(Setup\Config $config = null) : Setup\Goal {
		if (!($config instanceof \DatabaseSetupConfig)) {
			throw new \InvalidArgumentException(
				"Expected \\DatabaseSetupConfig, go '".get_class($config)."' instead."
			);
		}
		return new \DatabaseServerIsConnectableGoal($config);
	}

	/**
	 * @inheritdocs
	 */
	public function getUpdateGoal(Setup\Config $config = null) : Setup\Goal {
		throw new \LogicException("NYI!");
	}
}
