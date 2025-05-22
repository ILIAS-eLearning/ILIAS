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

namespace ILIAS\User\Profile;

use ILIAS\Language\Language;

/**
 * Personal profile publishing mode of a user
 * @author Alexander Killing <killing@leifos.de>
 */
class Mode
{
    public const PROFILE_DISABLED = 'n';
    public const PROFILE_ENABLED_LOGGED_IN_USERS = 'y';
    public const PROFILE_ENABLED_GLOBAL = 'g';

    public function __construct(
        private readonly Language $lng,
        private readonly \ilSetting $settings,
        private readonly \ilObjUser $user
    ) {
    }

    public function getMode(): string
    {
        $public_profile_pref = $this->user->prefs['public_profile'] ?? null;
        if ($public_profile_pref === null
            || !in_array(
                $public_profile_pref,
                [
                    self::PROFILE_ENABLED_LOGGED_IN_USERS,
                    self::PROFILE_ENABLED_GLOBAL
                ]
            )) {
            return self::PROFILE_DISABLED;
        }

        if ($this->settings->get('enable_global_profiles')) {
            return $public_profile_pref;
        }
        return self::PROFILE_ENABLED_LOGGED_IN_USERS;
    }

    public function isEnabled(): bool
    {
        return in_array(
            $this->getMode(),
            [
                self::PROFILE_ENABLED_LOGGED_IN_USERS,
                self::PROFILE_ENABLED_GLOBAL
            ]
        );
    }

    public function getModeInfo(?string $mode = null): string
    {
        switch ($this->getMode()) {
            case self::PROFILE_DISABLED:
                return $this->lng->txt('usr_public_profile_disabled');
            case self::PROFILE_ENABLED_LOGGED_IN_USERS:
                return $this->lng->txt('usr_public_profile_logged_in');
            case self::PROFILE_ENABLED_GLOBAL:
                return $this->lng->txt('usr_public_profile_global');
        }
        return '';
    }
}
