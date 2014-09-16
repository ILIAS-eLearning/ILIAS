<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilRuntime
 * @author  Michael Jansen <mjansen@databay.de>
 * @package Services/Environment
 */
final class ilRuntime
{
	/**
	 * @var self
	 */
	private static $instance = null;

	/**
	 * The runtime is a constant state during one request, so please use the public static getInstance() to instantiate the runtime
	 */
	private function __construct(){}

	/**
	 * @return self
	 */
	public static function getInstance()
	{
		if(self::$instance === null)
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Returns true when the runtime used is HHVM.
	 * @return bool
	 */
	public function isHHVM()
	{
		return defined('HHVM_VERSION');
	}

	/**
	 * Returns true when the runtime used is PHP.
	 * @return bool
	 */
	public function isPHP()
	{
		return !$this->isHHVM();
	}

	/**
	 * @return string
	 */
	public function getVersion()
	{
		if($this->isHHVM())
		{
			return HHVM_VERSION;
		}
		else
		{
			return PHP_VERSION;
		}
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		if($this->isHHVM())
		{
			return 'HHVM';
		}
		else
		{
			return 'PHP';
		}
	}

	/**
	 * A string representation of the runtime
	 */
	public function __toString()
	{
		return $this->getName() . ' ' . $this->getVersion();
	}

	/**
	 * @return int
	 */
	public function getReportedErrorLevels()
	{
		if($this->isHHVM())
		{
			return ini_get('hhvm.log.runtime_error_reporting_level');
		}
		else
		{
			return ini_get('error_reporting');
		}
	}

	/**
	 * @return boolean
	 */
	public function shouldLogErrors()
	{
		if($this->isHHVM())
		{
			return (bool)ini_get('hhvm.log.use_log_file');
		}
		else
		{
			return (bool)ini_get('log_errors');
		}
	}

	/**
	 * @return boolean
	 */
	public function shouldDisplayErrors()
	{
		if($this->isHHVM())
		{
			return (bool)ini_get('hhvm.debug.server_error_message');
		}
		else
		{
			return (bool)ini_get('display_errors');
		}
	}
}