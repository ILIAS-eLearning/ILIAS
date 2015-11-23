<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBuddySystem
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystem
{
	/**
	 * @var self
	 */
	protected static $instance;

	/**
	 * @var bool
	 */
	protected static $is_enabled;

	/**
	 * @var ilSetting
	 */
	protected $settings;

	/**
	 * 
	 */
	protected function __construct()
	{
		$this->settings =  new ilSetting('buddysystem');
	}

	/**
	 * @return self
	 */
	public static function getInstance()
	{
		if(!(self::$instance instanceof self))
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @param string $keyword
	 * @param mixed $value
	 */
	public function setSetting($keyword, $value)
	{
		$this->settings->set($keyword, $value);
	}

	/**
	 * @param string $keyword
	 * @param bool|false $default
	 * @return string
	 */
	public function getSetting($keyword, $default = false)
	{
		return $this->settings->get($keyword, $default);
	}

	/**
	 * @return bool
	 */
	public function isEnabled()
	{
		/**
		 * @var $ilUser ilObjUser
		 */
		global $ilUser;

		if(self::$is_enabled !== null)
		{
			return self::$is_enabled;
		}

		if($ilUser->isAnonymous())
		{
			self::$is_enabled = false;
			return false;
		}

		self::$is_enabled = $this->settings->get('enabled', false);
		return self::$is_enabled;
	}
}