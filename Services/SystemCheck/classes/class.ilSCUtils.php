<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/SystemCheck/classes/class.ilSCTask.php';

/**
 * Utilities for system check
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilSCUtils
{
    public static function taskStatus2Text($a_status)
    {
        global $DIC;

        $lng = $DIC['lng'];
        
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
    }
}
