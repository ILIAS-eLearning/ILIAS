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

namespace ILIAS\User;

use ILIAS\User\Profile\Profile;
use ILIAS\User\Search\Search;
use ILIAS\User\Settings\Settings;

class PublicInterface
{
    public function __construct(
        private readonly \ilObjUser $logged_in_user
    ) {
    }

    public function getSearch(): Search
    {
        return LocalDIC::dic()[Search::class];
    }

    public function getProfile(): Profile
    {
        return LocalDIC::dic()[Profile::class];
    }

    public function getSettings(): Settings
    {
        return LocalDIC::dic()[Settings::class];
    }

    public function getLoggedInUser(): \ilObjUser
    {
        return $this->logged_in_user;
    }
}
