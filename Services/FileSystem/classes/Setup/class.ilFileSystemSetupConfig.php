<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilFileSystemSetupConfig implements Setup\Config {
	/**
	 * @var string
	 */
	protected $data_dir;

	public function __construct(
		string $data_dir
	) {
		$this->data_dir = $data_dir;
	}

	public function getDataDir() : string {
		return $this->data_dir;
	}
}
