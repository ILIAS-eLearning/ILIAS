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
			return new ilMailMimeTransportNull();
		}

		if((bool)$this->settings->get('mail_smtp_status'))
		{
			return new ilMailMimeTransportSmtp($this->settings);
		}
		else
		{
			return new ilMailMimeTransportSendmail($this->settings);
		}
	}
}
