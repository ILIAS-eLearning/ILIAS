<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilHttpSetupConfig implements Setup\Config {
	/**
	 * @var	bool
	 */
	protected $autodetection_enabled;

	/**
	 * @var string|null
	 */
	protected $header_name;

	/**
	 * @var string|null
	 */
	protected $header_value;

	public function __construct(
		bool $autodetection_enabled,
		?string $header_name,
		?string $header_value
	) {
		if ($autodetection_enabled && (!$header_name || !$header_value)) {
			throw new \InvalidArgumentException(
				"Expected header name and value for https autodetection if that feature is enabled."
			);
		}
		$this->autodetection_enabled = $autodetection_enabled;
		$this->header_name = $header_name;
		$this->header_value = $header_value;
	}

	public function isAutodetectionEnabled() : bool {
		return $this->autodetection_enabled;
	}

	public function getHeaderName() : ?string {
		return $this->header_name;
	}

	public function getHeaderValue() : ?string {
		return $this->header_value;
	}
}
