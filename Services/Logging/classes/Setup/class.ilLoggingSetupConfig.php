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
		string $path_to_logfile,
		string $path_to_errorlogfiles
	) {
		if ($enable && !$path_to_logfile) {
			throw new \InvalidArgumentException(
				"Expected a path to the logfile, if logging is enabled."
			);
		}
		$this->enabled = $enabled;
		$this->path_to_logfile = $path_to_logfile;
		$this->path_to_errorlogfiles = $path_to_errorlogfiles;
	}

	public function isEnabled() : bool {
		return $this->enabled;
	}

	public function getPathToLogfile() : ?string {
		return $this->path_to_logfile;
	}

	public function getPathToErrorLogfiles() : ?string {
		return $this->path_to_errorlogfiles;
	}
}
