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

/**
 * Personal profile publishing mode of a user
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPersonalProfileMode
{
    public const PROFILE_DISABLED = "n";
    public const PROFILE_ENABLED_LOGGED_IN_USERS = "y";
    public const PROFILE_ENABLED_GLOBAL = "g";

    protected ilObjUser $user;
    protected ilSetting $settings;
    protected ilLanguage $lng;

    public function __construct(ilObjUser $user, ilSetting $settings)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->user = $user;
        $this->settings = $settings;
    }

    public function getMode(): string
    {
        $user = $this->user;
        $settings = $this->settings;

        $pub_prof = isset($user->prefs["public_profile"]) && in_array($user->prefs["public_profile"], [
            self::PROFILE_DISABLED,
            self::PROFILE_ENABLED_LOGGED_IN_USERS,
            self::PROFILE_ENABLED_GLOBAL
            ])
            ? $user->prefs["public_profile"]
            : self::PROFILE_DISABLED;
        if (!$settings->get('enable_global_profiles') && $pub_prof == self::PROFILE_ENABLED_GLOBAL) {
            $pub_prof = self::PROFILE_ENABLED_LOGGED_IN_USERS;
        }
        return $pub_prof;
    }

    /**
     * Is profile enabled
     */
    public function isEnabled(): bool
    {
        return in_array($this->getMode(), [self::PROFILE_ENABLED_LOGGED_IN_USERS,
            self::PROFILE_ENABLED_GLOBAL
        ]);
    }

    /**
     * Get mode info
     */
    public function getModeInfo(string $mode = null): string
    {
        $lng = $this->lng;

        if (is_null($mode)) {
            $mode = $this->getMode();
        }
        switch ($mode) {
            case self::PROFILE_DISABLED:
                return $lng->txt("usr_public_profile_disabled");
            case self::PROFILE_ENABLED_LOGGED_IN_USERS:
                return $lng->txt("usr_public_profile_logged_in");
            case self::PROFILE_ENABLED_GLOBAL:
                return $lng->txt("usr_public_profile_global");
        }
        return "";
    }
}
