<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Mail/interfaces/interface.ilMailRecipientParser.php';

/**
 * Class ilMailMimeSenderFactory
 */
class ilMailMimeSenderFactory
{
	/**
	 * @var \ilSetting
	 */
	protected $settings;

	/**
	 * ilMailMimeSenderFactory constructor.
	 * @param ilSetting $settings
	 */
	public function __construct(\ilSetting $settings)
	{
		$this->settings = $settings;
	}

	/**
	 * @param int $usrId
	 * @return bool
	 */
	protected function isSystemMail($usrId)
	{
		return $usrId == ANONYMOUS_USER_ID;
	}

	/**
	 * @param int $usrId
	 * @return ilMailMimeSender
	 */
	public function getSenderByUsrId($usrId)
	{
		// @todo mail_smtp: Cache / Flyweight
		switch(true)
		{
			case $this->isSystemMail($usrId):
				return $this->system();
				break;

			default:
				return $this->user($usrId);
				break;
		}
	}

	/**
	 * @return ilMailMimeSenderSystem
	 */
	public function system()
	{
		require_once 'Services/Mail/classes/Mime/Sender/class.ilMailMimeSenderSystem.php';
		return new ilMailMimeSenderSystem($this->settings);
	}

	/**
	 * @param int $usrId
	 * @return ilMailMimeSenderSystem
	 */
	public function user($usrId)
	{
		require_once 'Services/Mail/classes/Mime/Sender/class.ilMailMimeSenderUser.php';
		$sender = new ilMailMimeSenderUser($this->settings, $usrId);

		return $sender;
	}
}