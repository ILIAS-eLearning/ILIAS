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
