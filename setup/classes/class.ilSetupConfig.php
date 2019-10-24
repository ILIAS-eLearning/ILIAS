<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;
use ILIAS\Data\Password;

class ilSetupConfig implements Setup\Config {
	/**
	 * @var	Password
	 */
	protected $master_password;

	/**
	 * @var \DateTimeZone
	 */
	protected $server_timezone;

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

	/**
	 * @var string|null
	 */
	protected $path_to_ghostscript;

	/**
	 * @var string|null
	 */
	protected $path_to_ffmpeg;

	/**
	 * @var string|null
	 */
	protected $path_to_phantom_js;

	/**
	 * @var string|null
	 */
	protected $path_to_latex_cgi;

	public function __construct(
		Password $master_password,
		\DateTimeZone $server_timezone,
		string $path_to_convert,
		string $path_to_zip,
		string $path_to_unzip,
		?string $path_to_ghostscript,
		?string $path_to_ffmpeg,
		?string $path_to_phantom_js,
		?string $path_to_latex_cgi
	) {
		$this->master_password = $master_password;
		$this->server_timezone = $server_timezone;
		$this->path_to_convert = $this->toLinuxConvention($path_to_convert);
		$this->path_to_zip = $this->toLinuxConvention($path_to_zip);
		$this->path_to_unzip = $this->toLinuxConvention($path_to_unzip);
		$this->path_to_ghostscript = $this->toLinuxConvention($path_to_ghostscript);
		$this->path_to_ffmpeg = $this->toLinuxConvention($path_to_ffmpeg);
		$this->path_to_phantom_js = $this->toLinuxConvention($path_to_phantom_js);
		$this->path_to_latex_cgi = $this->toLinuxConvention($path_to_latex_cgi);
	}

	protected function toLinuxConvention(?string $p) : ?string {
		if (!$p) {
			return null;
		}
		return preg_replace("/\\\\/","/",$p);
	}

	public function getMasterPassword() : Password {
		return $this->master_password;
	}

	public function getServerTimeZone() : \DateTimeZone {
		return $this->server_timezone;
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

	public function getPathToGhostscript() : string {
		return $this->path_to_ghostscript;
	}

	public function getPathToFFMPEG() : string {
		return $this->path_to_ffmpeg;
	}

	public function getPathToPhantomJS() : ?string {
		return $this->path_to_phantom_js;
	}

	public function getPathToLatexCGI() : ?string {
		return $this->path_to_latex_cgi;
	}
}
