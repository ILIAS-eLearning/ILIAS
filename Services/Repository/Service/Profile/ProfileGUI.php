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
    protected \ilLanguage $lng;
    protected \ILIAS\UI\Factory $ui_factory;

    public function __construct(
        \ilLanguage $lng,
        \ILIAS\UI\Factory $ui_factory
    ) {
        $this->ui_factory = $ui_factory;
        $this->lng = $lng;
    }

    public function getAvatar(int $user_id): Avatar
    {
        if (\ilObject::_lookupType($user_id) === 'usr') {
            $avatar = \ilObjUser::_getAvatar($user_id);
        } else {
            $avatar = $this->ui_factory->symbol()->avatar()->letter($this->lng->txt("deleted"));
        }
        return $avatar;
    }

    public function getNamePresentation(int $user_id, bool $link_profile = false, string $back = ""): string
    {
        if (\ilObject::_lookupType($user_id) === 'usr') {
            $name = \ilUserUtil::getNamePresentation($user_id, false, $link_profile, $back);
        } else {
            $name = $this->lng->txt("deleted_user");
        }
        return $name;
    }
}
