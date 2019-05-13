<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;
use ILIAS\Data\Password;

class DatabaseSetupConfig implements Setup\Config {
	/**
	 * @var	mixed
	 */
	protected $type;

	/**
	 * @var string
	 */
	protected $host;

	/**
	 * @var string|null
	 */
	protected $port;

	/**
	 * @var	string
	 */
	protected $database;

	/**
	 * @var bool
	 */
	protected $create_database;

	/**
	 * @var	string
	 */
	protected $user;

	/**
	 * @var	Password|null
	 */
	protected $password;

	public function __construct(
		$type,
		string $host,
		string $database,
		string $user,
		Password $password = null,
		bool $create_database = false,
		string $port = null
	) {
		if (!in_array($type, \ilDBConstants::getInstallableTypes())) {
			throw new \InvalidArgumentException(
				"Unknown database type: $type"
			);
		}
		$this->type = trim($type);
		$this->host = trim($host);
		$this->database = trim($database);
		$this->user = trim($user);
		$this->password = $password;
		$this->create_database = trim($create_database);
		$this->port = $port;
	}

	public function getType() {
		return $this->type;
	}

	public function getHost() : string {
		return $this->host;
	}

	public function getPort() : ?string {
		return $this->port;
	}

	public function getDatabase() : string {
		return $this->database;
	}

	public function getCreateDatabase() : bool {
		return $this->create_database;
	}

	public function getUser() : string {
		return $this->user;
	}

	public function getPassword() : ?Password {
		return $this->password;
	}

	/**
	 * Adapter to current database-handling via a mock of \ilIniFile.
	 */
	public function toMockIniFile() : \ilIniFile {
		return new class($this) extends \ilIniFile {
			/**
			* reads a single variable from a group
			* @access	public
			* @param	string		group name
			* @param	string		value
			* @return	mixed		return value string or boolean 'false' on failure
			*/
			function readVariable($a_group, $a_var_name)
			{
				if ($a_group !== "db") {
					throw new \LogicException(
						"Can only access db-config via this mock."
					);
				}
				switch ($a_var_name) {
					case "user":
						return $this->config->getUser();
					case "host":
						return $this->config->getHost();
					case "port":
						return $this->config->getPort();
					case "pass":
						return $this->config->getPassword()->toString();
					case "name":
						return $this->config->getDatabase();
					case "type":
						return $this->config->getType();
					default:
						throw new \LogicException(
							"Cannot provide variable '$a_varname'"
						);
				}
			}
			function __construct(\DatabaseSetupConfig $config) { $this->config = $config; }
			function read() { throw new \LogicException("Just a mock here..."); }
			function parse() { throw new \LogicException("Just a mock here..."); }
			function fixIniFile() { throw new \LogicException("Just a mock here..."); }
			function write() { throw new \LogicException("Just a mock here..."); }
			function show() { throw new \LogicException("Just a mock here..."); }
			function getGroupCount() { throw new \LogicException("Just a mock here..."); }
			function readGroups() { throw new \LogicException("Just a mock here..."); }
			function groupExists($a_group_name) { throw new \LogicException("Just a mock here..."); }
			function readGroup($a_group_name) { throw new \LogicException("Just a mock here..."); }
			function addGroup($a_group_name) { throw new \LogicException("Just a mock here..."); }
			function removeGroup($a_group_name) { throw new \LogicException("Just a mock here..."); }
			function variableExists($a_group, $a_var_name)  { throw new \LogicException("Just a mock here..."); }
			function setVariable($a_group_name, $a_var_name, $a_var_value) { throw new \LogicException("Just a mock here..."); }
			function error($a_errmsg) { throw new \LogicException("Just a mock here..."); }
			function getError() { throw new \LogicException("Just a mock here..."); }
		};
	}
}
