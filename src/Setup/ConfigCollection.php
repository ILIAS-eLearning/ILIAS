<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup;

/**
 * A collection of some configurations.
 */
class ConfigCollection implements Config {
	/**	
	 * @var array<string,Config>
	 */
	protected $configs;

	public function __construct(array $configs) {
		$this->configs = $configs;
	}

	public function getConfig(string $key) : Config {
		if (!isset($this->configs[$key])) {
			throw new \InvalidArgumentException(
				"Unknown key '$key' for Config."
			);
		}
		return $this->configs[$key];
	}
}
