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
    public static function _checkGoto(string $target) : bool
    {
        global $DIC;

        $ilAccess = $DIC->access();

        $target = SYSTEM_FOLDER_ID;

        if ($ilAccess->checkAccess("read", "", $target)) {
            return true;
        }
        return false;
    }
}
