<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @noinspection PhpIncludeInspection */
require_once './Services/Object/classes/class.ilObjectAccess.php';

/**
 * Class ilObjWorkflowEngineAccess
 *
 * @author Maximilian Becker <mbecker@databay.de>
 *
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilObjWorkflowEngineAccess extends ilObjectAccess
{
    /**
     * checks whether a user may invoke a command or not
     * (this method is called by ilAccessHandler::checkAccess)
     */
    public function _checkAccess(string $cmd, string $permission, int $ref_id, int $obj_id, ?int $user_id = null) : bool
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
                break;

            case 'leave':
        }

        // Deal with permissions
        switch ($permission) {
            case 'visible':
                    return $rbacsystem->checkAccessOfUser($user_id, 'visible', $ref_id);
                break;

            case 'read':
                    return $rbacsystem->checkAccessOfUser($user_id, 'write', $ref_id);
                break;
        }

        return true; // ORLY?
    }

    /**
     * @param string $target
     *
     * @return bool
     */
    public static function _checkGoto(string $target) : bool
    {
        //$workflow = substr($params, 2, strpos($params,'EVT')-2);
        //$event = substr($params, strpos($params, 'EVT')+3);
        return true; // TODO Validate Event Syntax
    }
}
