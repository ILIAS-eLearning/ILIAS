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

class ProfileAdapter
{
    protected \ilLanguage $lng;

    public function __construct(
        \ilLanguage $lng
    ) {
        $this->lng = $lng;
    }

    public function exists(int $user_id): bool
    {
        return \ilObjUser::userExists([$user_id]);
    }

    public function getDeletedUserAvatarText(): string
    {
        return $this->lng->txt("deleted");
    }

    public function getDeletedUserNamePresentation(): string
    {
        return $this->lng->txt("deleted_user");
    }
}
