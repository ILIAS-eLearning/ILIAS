<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilMailMimeSenderUserById
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailMimeSenderUserById extends ilMailMimeSenderUser
{
    /** @var array<int, ilObjUser> */
    protected static array $userInstances = [];

    public function __construct(ilSetting $settings, int $usrId)
    {
        if (!array_key_exists($usrId, self::$userInstances)) {
            self::$userInstances[$usrId] = new ilObjUser($usrId);
        }

        parent::__construct($settings, self::$userInstances[$usrId]);
    }

    public static function addUserToCache(int $usrId, ilObjUser $user): void
    {
        self::$userInstances[$usrId] = $user;
    }
}
