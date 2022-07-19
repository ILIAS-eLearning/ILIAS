<?php declare(strict_types=1);
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
 * Utilities for system check
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilSCUtils
{
    public static function taskStatus2Text(int $a_status) : string
    {
        global $DIC;

        $lng = $DIC->language();

        switch ($a_status) {
            case ilSCTask::STATUS_NOT_ATTEMPTED:
                return $lng->txt('sysc_status_na');

            case ilSCTask::STATUS_IN_PROGRESS:
                return $lng->txt('sysc_status_running');

            case ilSCTask::STATUS_FAILED:
                return $lng->txt('sysc_status_failed');

            case ilSCTask::STATUS_COMPLETED:
                return $lng->txt('sysc_status_completed');

        }
        return '';
    }
}
