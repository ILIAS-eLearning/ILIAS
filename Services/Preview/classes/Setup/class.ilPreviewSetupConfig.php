<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilPreviewSetupConfig implements Setup\Config {
	/**
	 * @var string|null
	 */
	protected $path_to_ghostscript;

	public function __construct(
		?string $path_to_ghostscript
	) {
		$this->path_to_ghostscript = $this->toLinuxConvention($path_to_ghostscript);
	}

	protected function toLinuxConvention(?string $p) : ?string {
		if (!$p) {
			return null;
		}
		return preg_replace("/\\\\/","/",$p);
	}

	public function getPathToGhostscript() : ?string {
		return $this->path_to_ghostscript;
	}
}
