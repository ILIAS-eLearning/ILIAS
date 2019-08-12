<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailMimeSenderUserByEmailAddress
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailMimeSenderUserByEmailAddress extends \ilMailMimeSenderUser
{
	/**
	 * ilMailMimeSenderUserByEmailAddress constructor.
	 * @param \ilSetting $settings
	 * @param string $emailAddress
	 */
	public function __construct(\ilSetting $settings, string $emailAddress)
	{
		$user = new \ilObjUser();
		$user->setEmail($emailAddress);

		parent::__construct($settings, $user);
	}
}