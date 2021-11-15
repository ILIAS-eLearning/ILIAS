<?php declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

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
