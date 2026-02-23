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

namespace ILIAS\Help\GuidedTour\Elements;

use ilDBInterface;
use ILIAS\Help\GuidedTour\InternalDataService;
use ILIAS\Help\GuidedTour\InternalRepoService;
use ilObjUser;
use ILIAS\Help\GuidedTour\InternalDomainService;

class IdPresentation
{
    protected \ilSetting $gdtr_admin_setting;

    public function __construct(
        protected InternalDomainService $domain
    ) {
        $this->gdtr_admin_setting = new \ilSetting("gdtr");
    }

    public function saveIdPresentationUsers(string $users): void
    {
        $this->gdtr_admin_setting->set("id_presentation_users", $users);
    }

    public function getIdPresentationUsers(): string
    {
        return $this->gdtr_admin_setting->get("id_presentation_users", "");
    }

    public function getValidIdPresentationUsers(): array
    {
        $valid_users = [];
        foreach (explode(",", $this->getIdPresentationUsers()) as $user) {
            if (\ilObjUser::_loginExists(trim($user))) {
                $valid_users[] = trim($user);
            }
        }
        return $valid_users;
    }

}
