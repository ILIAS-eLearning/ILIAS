<?php

declare(strict_types=1);

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
 * Class ilObjMailAccess
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilObjMailAccess extends ilObjectAccess
{
    public static function _checkGoto(string $target): bool
    {
        global $DIC;

        $mail = new ilMail($DIC->user()->getId());
        if ($DIC->rbac()->system()->checkAccess('internal_mail', $mail->getMailObjectReferenceId())) {
            return true;
        }

        return false;
    }
}
