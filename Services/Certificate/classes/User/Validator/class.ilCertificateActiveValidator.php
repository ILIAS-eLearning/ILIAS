<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateActiveValidator
{
	/**
	 * @var ilSetting|null
	 */
	private $setting;

	/**
	 * @param ilSetting|null $setting
	 */
	public function __construct(ilSetting $setting = null)
	{
		if (null === $setting) {
			$setting = new ilSetting("certificate");
		}
		$this->setting = $setting;
	}

	public function validate()
	{
		$globalCertificateActive = (bool)$this->setting->get("active");

		if (false === $globalCertificateActive) {
			return false;
		}

		$serverActive = (bool) ilRPCServerSettings::getInstance()->isEnabled();

		if (false === $serverActive) {
			return false;
		}

		return true;

	}
}
