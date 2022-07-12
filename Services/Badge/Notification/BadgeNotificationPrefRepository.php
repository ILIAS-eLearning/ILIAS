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

namespace ILIAS\Badge\Notification;

/**
 * Badge notification repository
 * (using user preferences
 * @author Alexander Killing <killing@leifos.de>
 */
class BadgeNotificationPrefRepository
{
    protected \ilObjUser $user;

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
    public function updateLastCheckedTimestamp() : void
    {
        $this->user->writePref("badge_last_checked", (string) time());
    }
    
    /**
     * Get last checked timestamp
     */
    public function getLastCheckedTimestamp() : int
    {
        return (int) $this->user->getPref("badge_last_checked");
    }
}
