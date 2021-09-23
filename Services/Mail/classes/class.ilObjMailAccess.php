<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilObjMailAccess
*
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*/
class ilObjMailAccess extends ilObjectAccess
{
    /**
     * Returns the number of attachments and the number of bytes used on the
     * harddisk for mail attachments, by the user with the specified user id.
     * @return array('count'=>integer,'size'=>integer),...)
     *                            // an associative array with the disk
     *                            // usage in bytes for each object type
     */
    public function _lookupDiskUsageOfUser(int $user_id) : array
    {
        return ilFileDataMail::_lookupDiskUsageOfUser($user_id);
    }


    public static function _checkGoto($a_target) : bool
    {
        $mail = new ilMail($GLOBALS['DIC']['ilUser']->getId());
        if ($GLOBALS['DIC']['rbacsystem']->checkAccess('internal_mail', $mail->getMailObjectReferenceId())) {
            return true;
        }
        return false;
    }
}
