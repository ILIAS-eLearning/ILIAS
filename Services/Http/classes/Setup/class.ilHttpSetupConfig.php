<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilHttpSetupConfig implements Setup\Config {
	/**
	 * @var	string
	 */
	protected $http_path;

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

	/**
	 * @var bool
	 */
	protected $proxy_enabled;

	/**
	 * @var string|null
	 */
	protected $proxy_host;

	/**
	 * @var string|null
	 */
	protected $proxy_port;


	public function __construct(
		string $http_path,
		bool $autodetection_enabled,
		?string $header_name,
		?string $header_value,
		bool $proxy_enabled,
		?string $proxy_host,
		?string $proxy_port
	) {
		if ($autodetection_enabled && (!$header_name || !$header_value)) {
			throw new \InvalidArgumentException(
				"Expected header name and value for https autodetection if that feature is enabled."
			);
		}
		if ($proxy_enabled && (!$proxy_host || !$proxy_port)) {
			throw new \InvalidArgumentException(
				"Expected setting for proxy host and port if proxy is enabled."
			);
		}
		$this->http_path = $http_path;
		$this->autodetection_enabled = $autodetection_enabled;
		$this->header_name = $header_name;
		$this->header_value = $header_value;
		$this->proxy_enabled = $proxy_enabled;
		$this->proxy_host = $proxy_host;
		$this->proxy_port = $proxy_port;
	}

	public function getHttpPath() : string {
		return $this->http_path;
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

	public function isProxyEnabled() : bool {
		return $this->proxy_enabled;
	}

	public function getProxyHost() : ?string {
		return $this->proxy_host;
	}

	public function getProxyPort() : ?string {
		return $this->proxy_port;
	}
}
