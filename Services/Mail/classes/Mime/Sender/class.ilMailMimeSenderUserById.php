<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailMimeSenderUserById
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailMimeSenderUserById extends ilMailMimeSenderUser
{
    /** @var ilObjUser[] */
    protected static array $userInstances = [];

    public function __construct(ilSetting $settings, int $usrId)
    {
        if (!array_key_exists($usrId, self::$userInstances)) {
            self::$userInstances[$usrId] = new ilObjUser($usrId);
        }

        parent::__construct($settings, self::$userInstances[$usrId]);
    }

    public static function addUserToCache(int $usrId, ilObjUser $user) : void
    {
        self::$userInstances[$usrId] = $user;
    }
}
