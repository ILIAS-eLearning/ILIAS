<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

abstract class DatabaseGoal implements Setup\Goal {
	/**
	 * @var	DatabaseSetupConfig
	 */
	protected $config;

	public function __construct(\DatabaseSetupConfig $config) {
		$this->config = $config;
	}
}
