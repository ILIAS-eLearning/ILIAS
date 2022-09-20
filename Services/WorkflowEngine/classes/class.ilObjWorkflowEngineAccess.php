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
 * Class ilObjWorkflowEngineAccess
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
class ilObjWorkflowEngineAccess extends ilObjectAccess
{
    /**
     * checks whether a user may invoke a command or not
     * (this method is called by ilAccessHandler::checkAccess)
     */
    public function _checkAccess(string $cmd, string $permission, int $ref_id, int $obj_id, ?int $user_id = null): bool
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];
        $lng = $DIC['lng'];
        $ilUser = $DIC['ilUser'];
        $ilAccess = $DIC['ilAccess'];

        if (is_null($user_id)) {
            $user_id = $ilUser->getId();
        }

        // Deal with commands
        switch ($cmd) {
            case "view":
                $ilAccess->addInfoItem(ilAccessInfo::IL_NO_OBJECT_ACCESS, $lng->txt("crs_status_blocked"));
                return false;

            case 'leave':
        }

        // Deal with permissions
        switch ($permission) {
            case 'visible':
                return $rbacsystem->checkAccessOfUser($user_id, 'visible', $ref_id);

            case 'read':
                return $rbacsystem->checkAccessOfUser($user_id, 'write', $ref_id);
        }

        return true; // ORLY?
    }

    public static function _checkGoto(string $target): bool
    {
        //$workflow = substr($params, 2, strpos($params,'EVT')-2);
        //$event = substr($params, strpos($params, 'EVT')+3);
        return true; // TODO Validate Event Syntax
    }
}
