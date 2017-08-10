<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilMailMimeTransportFactory
{
	/**
	 * @var \ilSetting
	 */
	protected $settings;

	/**
	 * ilMailMimeTransportFactory constructor.
	 * @param ilSetting $settings
	 */
	public function __construct(\ilSetting $settings)
	{
		$this->settings = $settings;
	}

	/**
	 * @return ilMailMimeTransport
	 */
	public function getTransport()
	{
		if(!(bool)$this->settings->get('mail_allow_external'))
		{
			require_once 'Services/Mail/classes/Mime/Transport/class.ilMailMimeTransportNull.php';
			return new ilMailMimeTransportNull();
		}

		if((bool)$this->settings->get('mail_smtp_status'))
		{
			require_once 'Services/Mail/classes/Mime/Transport/class.ilMailMimeTransportSmtp.php';
			return new ilMailMimeTransportSmtp($this->settings);
		}
		else
		{
			require_once 'Services/Mail/classes/Mime/Transport/class.ilMailMimeTransportSendmail.php';
			return new ilMailMimeTransportSendmail($this->settings);
		}
	}
}