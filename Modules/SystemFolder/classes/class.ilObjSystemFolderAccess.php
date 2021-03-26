<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Class ilObjRootFolderAccess
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilObjSystemFolderAccess extends ilObjectAccess
{
    /**
     * check whether goto script will succeed
     */
    public static function _checkGoto($a_target)
    {
        global $DIC;

        $ilAccess = $DIC->access();

        $a_target = SYSTEM_FOLDER_ID;

        if ($ilAccess->checkAccess("read", "", $a_target)) {
            return true;
        }
        return false;
    }
}
