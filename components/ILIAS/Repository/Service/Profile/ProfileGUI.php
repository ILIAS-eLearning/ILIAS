<?php

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

declare(strict_types=1);

namespace ILIAS\Repository\Profile;

use ILIAS\UI\Component\Symbol\Avatar\Avatar;

class ProfileGUI
{
    protected ProfileAdapter $profile;
    protected \ILIAS\UI\Factory $ui_factory;

    public function __construct(
        ProfileAdapter $profile,
        \ILIAS\UI\Factory $ui_factory
    ) {
        $this->ui_factory = $ui_factory;
        $this->profile = $profile;
    }

    public function getAvatar(int $user_id): Avatar
    {
        if ($this->profile->exists($user_id)) {
            $avatar = \ilObjUser::_getAvatar($user_id);
        } else {
            $avatar = $this->ui_factory->symbol()->avatar()->letter($this->profile->getDeletedUserAvatarText());
        }
        return $avatar;
    }

    public function getPicturePath(int $user_id): string
    {
        global $DIC;

        if ($this->profile->exists($user_id)) {
            return \ilObjUser::_getPersonalPicturePath($user_id, "xsmall", true, true);
        }
        $fac = new \ilUserAvatarFactory($DIC);
        $avatar = $fac->avatar("xsmall");
        $avatar->setName(substr($this->profile->getDeletedUserAvatarText(), 0, 2));
        $avatar->setUsrId($user_id);
        return $avatar->getUrl();
    }

    public function getNamePresentation(int $user_id, bool $link_profile = false, string $back = "", $force_first_last = false): string
    {
        if ($this->profile->exists($user_id)) {
            $name = \ilUserUtil::getNamePresentation($user_id, false, $link_profile, $back, $force_first_last);
        } else {
            $name = $this->profile->getDeletedUserNamePresentation();
        }
        return $name;
    }
}
