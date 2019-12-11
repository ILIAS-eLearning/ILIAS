<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Personal profile publishing mode of a iser
 *
 * @author killing@leifos.de
 */
class ilPersonalProfileMode
{
    const PROFILE_DISABLED = "n";
    const PROFILE_ENABLED_LOGGED_IN_USERS = "y";
    const PROFILE_ENABLED_GLOBAL = "g";

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilSetting
     */
    protected $settings;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * Constructor
     */
    public function __construct(ilObjUser $user, ilSetting $settings)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->user = $user;
        $this->settings = $settings;
    }

    /**
     * Get mode
     *
     * @return string
     */
    public function getMode() : string
    {
        $user = $this->user;
        $settings = $this->settings;

        $pub_prof = in_array($user->prefs["public_profile"], [
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
     *
     * @return bool
     */
    public function isEnabled() : bool
    {
        return in_array($this->getMode(), [self::PROFILE_ENABLED_LOGGED_IN_USERS,
            self::PROFILE_ENABLED_GLOBAL
        ]);
    }
    
    /**
     * Get mode info
     *
     * @param string|null $mode
     * @return string
     */
    public function getModeInfo(string $mode = null) : string
    {
        $lng = $this->lng;

        if (is_null($mode)) {
            $mode = $this->getMode();
        }
        switch ($mode) {
            case self::PROFILE_DISABLED:
                return $lng->txt("usr_public_profile_disabled");
                break;
            case self::PROFILE_ENABLED_LOGGED_IN_USERS:
                return $lng->txt("usr_public_profile_logged_in");
                break;
            case self::PROFILE_ENABLED_GLOBAL:
                return $lng->txt("usr_public_profile_global");
                break;
        }
    }
}
