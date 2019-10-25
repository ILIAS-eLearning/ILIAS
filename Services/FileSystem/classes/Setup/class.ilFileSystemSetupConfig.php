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
		$this->data_dir = $this->normalizePath($data_dir);
	}

	protected function normalizePath(string $p) : string {
		$p = preg_replace("/\\\\/","/",$p);
		return preg_replace("%/+$%","",$p);
	}

	public function getDataDir() : string {
		return $this->data_dir;
	}
}
