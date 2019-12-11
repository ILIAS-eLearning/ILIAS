<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Badge\Notification;

/**
 * Badge notification repository
 * (using user preferences
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class BadgeNotificationPrefRepository
{
    /**
     * @var \ilObjUser
     */
    protected $user;

    /**
     * Constructor
     */
    public function __construct(\ilObjUser $user = null)
    {
        global $DIC;

        $this->user = (is_null($user))
            ? $DIC->user()
            : $user;
    }

    /**
     * Set last checked timestamp
     */
    public function updateLastCheckedTimestamp()
    {
        $this->user->writePref("badge_last_checked", time());
    }
    
    /**
     * Get last checked timestamp
     *
     * @return int
     */
    public function getLastCheckedTimestamp()
    {
        return (int) $this->user->getPref("badge_last_checked");
    }
}
