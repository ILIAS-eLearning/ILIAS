<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilLoggingSetupConfig implements Setup\Config {
	/**
	 * @var bool
	 */
	protected $enabled;

	/**
	 * @var string|null
	 */
	protected $path_to_logfile;

	/**
	 * @var string|null
	 */
	protected $path_to_errorlogfiles;

	public function __construct(
		bool $enabled,
		?string $path_to_logfile,
		?string $errorlog_dir
	) {
		if ($enabled && !$path_to_logfile) {
			throw new \InvalidArgumentException(
				"Expected a path to the logfile, if logging is enabled."
			);
		}
		$this->enabled = $enabled;
		$this->path_to_logfile = $this->normalizePath($path_to_logfile);
		$this->errorlog_dir = $this->normalizePath($errorlog_dir);
	}

	protected function normalizePath(?string $p) : ?string {
		if (!$p) {
			return null;
		}
		$p = preg_replace("/\\\\/","/",$p);
		return preg_replace("%/+$%","",$p);
	}

	public function isEnabled() : bool {
		return $this->enabled;
	}

	public function getPathToLogfile() : ?string {
		return $this->path_to_logfile;
	}

	public function getErrorlogDir() : ?string {
		return $this->errorlog_dir;
	}
}
