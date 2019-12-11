<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilUtilitiesSetupConfig implements Setup\Config {
	/**
	 * @var string
	 */
	protected $path_to_convert;

	/**
	 * @var string
	 */
	protected $path_to_zip;

	/**
	 * @var string
	 */
	protected $path_to_unzip;

	public function __construct(
		string $path_to_convert,
		string $path_to_zip,
		string $path_to_unzip
	) {
		$this->path_to_convert = $this->toLinuxConvention($path_to_convert);
		$this->path_to_zip = $this->toLinuxConvention($path_to_zip);
		$this->path_to_unzip = $this->toLinuxConvention($path_to_unzip);
	}

	protected function toLinuxConvention(?string $p) : ?string {
		if (!$p) {
			return null;
		}
		return preg_replace("/\\\\/","/",$p);
	}

	public function getPathToConvert() : string {
		return $this->path_to_convert;
	}

	public function getPathToZip() : string {
		return $this->path_to_zip;
	}

	public function getPathToUnzip() : string {
		return $this->path_to_unzip;
	}
}
