<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Mail/classes/Mime/Sender/interface.ilMailMimeSender.php';

/**
 * Class ilMailMimeSenderSystem
 */
class ilMailMimeSenderUser implements ilMailMimeSender
{
	/**
	 * @var \ilSetting
	 */
	protected $settings;

	/**
	 * @var
	 */
	protected $usrId;

	/**
	 * ilMailMimeSenderSystem constructor.
	 * @param ilSetting $settings
	 * @param int       $usrId
	 */
	public function __construct(\ilSetting $settings, $usrId)
	{
		$this->settings = $settings;
		$this->usrId    = $usrId;
	}

	/**
	 * @inheritdoc
	 */
	public function hasReplyToAddress()
	{
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function getReplyToAddress()
	{
		// @todo mail_smtp: 
		return 'gvollbach@databay.de';
	}

	/**
	 * @inheritdoc
	 */
	public function getReplyToName()
	{
		// @todo mail_smtp: 
		return 'Guido Vollbach';
	}

	/**
	 * @inheritdoc
	 */
	public function hasEnvelopFromAddress()
	{
		return strlen($this->settings->get('mail_system_usr_head_env_from_addr')) > 0;
	}

	/**
	 * @inheritdoc
	 */
	public function getEnvelopFromAddress()
	{
		return $this->settings->get('mail_system_usr_head_env_from_addr');
	}

	/**
	 * @inheritdoc
	 */
	public function getFromAddress()
	{
		return $this->settings->get('mail_system_usr_from_addr');
	}

	/**
	 * @inheritdoc
	 */
	public function getFromName()
	{
		// @todo mail_smtp: Replace Placeholders
		return $this->settings->get('mail_system_usr_from_name');
	}
}