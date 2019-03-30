<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailMimeSenderUserById
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailMimeSenderUserById extends \ilMailMimeSenderUser
{
	/** @var \ilObjUser[] */
	protected static $userInstances = [];

	/**
	 * ilMailMimeSenderUserById constructor.
	 * @param \ilSetting $settings
	 * @param int $usrId
	 */
	public function __construct(\ilSetting $settings, int $usrId)
	{
		if (!array_key_exists($usrId, self::$userInstances)) {
			self::$userInstances[$usrId] = new \ilObjUser($usrId);
		}

		parent::__construct($settings, self::$userInstances[$usrId]);
	}

	/**
	 * @param int $usrId
	 * @param \ilObjUser $user
	 */
	public static function addUserToCache(int $usrId, \ilObjUser $user)
	{
		self::$userInstances[$usrId] = $user;
	}
}