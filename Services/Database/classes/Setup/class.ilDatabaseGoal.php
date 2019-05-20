<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

abstract class ilDatabaseGoal implements Setup\Goal {
	/**
	 * @var	ilDatabaseSetupConfig
	 */
	protected $config;

	public function __construct(\ilDatabaseSetupConfig $config) {
		$this->config = $config;
	}
}
