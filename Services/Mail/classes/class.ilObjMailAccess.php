<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilObjMailAccess
* @author Alex Killing <alex.killing@gmx.de>
*/
class ilObjMailAccess extends ilObjectAccess
{
    public static function _checkGoto(string $target) : bool
    {
        global $DIC;

        $mail = new ilMail($DIC->user()->getId());
        if ($DIC->rbac()->system()->checkAccess('internal_mail', $mail->getMailObjectReferenceId())) {
            return true;
        }

        return false;
    }
}
