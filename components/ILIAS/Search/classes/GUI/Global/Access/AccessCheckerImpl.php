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

namespace ILIAS\Search\GUI\Global;

use ilRbacSystem;
use ilSearchSettings;
use ilObjUser;

class AccessCheckerImpl implements AccessChecker
{
    public function __construct(
        protected ilObjUser $user,
        protected ilRbacSystem $rbac_system,
        protected ilSearchSettings $settings
    ) {
    }

    public function canAccessObjectSearch(): bool
    {
        return $this->canAccessSearch();
    }

    public function canAccessUserSearch(): bool
    {
        return $this->canAccessSearch() &&
            $this->user->getId() !== ANONYMOUS_USER_ID &&
            $this->settings->enabledLucene() &&
            $this->settings->isLuceneUserSearchEnabled();
    }

    protected function canAccessSearch(): bool
    {
        return $this->rbac_system->checkAccess('search', ilSearchSettings::_getSearchSettingRefId());
    }
}
