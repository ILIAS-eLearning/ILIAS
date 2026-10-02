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

namespace ILIAS\Search\Result\Filter\Criterion;

use ILIAS\User\Settings\Settings as UserPreferences;
use ilObjUser;
use ILIAS\User\Profile\Profile as UserProfile;

class UserIsPublic implements Criterion
{
    protected array $preloaded_profile_data = [];

    public function __construct(
        protected UserPreferences $user_preferences,
        protected UserProfile $user_profile
    ) {
    }

    public function preloadData(int ...$ids): void
    {
        foreach ($this->user_profile->getDataForMultiple($ids) as $data) {
            $system_info = $data->getSystemInformation();
            $id = $data->getId();
            $this->preloaded_profile_data[$id]['active'] = $system_info['active'];
            $this->preloaded_profile_data[$id]['time_limit_unlimited'] = $system_info['time_limit_unlimited'];
            $this->preloaded_profile_data[$id]['time_limit_from'] = $system_info['time_limit_from'];
            $this->preloaded_profile_data[$id]['time_limit_until'] = $system_info['time_limit_until'];
        }
    }

    public function doesFulfill(int $id): bool
    {
        if ($id === ANONYMOUS_USER_ID) {
            return false;
        }

        $public_profile = $this->user_preferences->getSettingValueFor($id, 'public_profile') ?? '';
        if ($public_profile !== 'y' && $public_profile !== 'g') {
            return false;
        }

        if (!($this->preloaded_profile_data[$id]['active'] ?? false)) {
            return false;
        }

        if (
            !($this->preloaded_profile_data[$id]['time_limit_unlimited'] ?? false) &&
            (
                ($this->preloaded_profile_data[$id]['time_limit_from'] ?? 0) >= time() ||
                ($this->preloaded_profile_data[$id]['time_limit_until'] ?? 0) <= time()
            )
        ) {
            return false;
        }

        return true;
    }
}
