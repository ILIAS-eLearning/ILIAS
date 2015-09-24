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
		$this->settings =  new ilSetting('pd');
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
	 * @return bool
	 */
	public function isEnabled()
	{
		/**
		 * @var $ilUser     ilObjUser
		 * @var $rbacsystem ilRbacSystem
		 */
		global $ilUser, $rbacsystem;

		if(self::$is_enabled !== null)
		{
			return self::$is_enabled;
		}

		if($ilUser->isAnonymous())
		{
			self::$is_enabled = false;
			return false;
		}

		$awrn_set = new ilSetting('awrn');
		if($awrn_set->get('awrn_enabled', false))
		{
			self::$is_enabled = true;
			return true;
		}


		if($rbacsystem->checkAccess('internal_mail', ilMailGlobalServices::getMailObjectRefId()))
		{
			self::$is_enabled = true;
			return true;
		}

		self::$is_enabled = false;
		return false;
	}
}