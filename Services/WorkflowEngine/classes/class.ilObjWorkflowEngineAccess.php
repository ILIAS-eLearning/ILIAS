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
     * checks wether a user may invoke a command or not
     * (this method is called by ilAccessHandler::checkAccess)
     *
     * @param	string $cmd        command (not permission!)
     * @param	string $permission permission
     * @param	int    $ref_id     reference id
     * @param	int    $a_obj_id   object id
     * @param	int    $user_id    user id (if not provided, current user is taken)
     *
     * @return	boolean		true, if everything is ok
     */
    public function _checkAccess($cmd, $permission, $ref_id, $a_obj_id, $user_id = "")
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];
        $lng = $DIC['lng'];
        $ilUser = $DIC['ilUser'];
        $ilAccess = $DIC['ilAccess'];

        if ($user_id == "") {
            $user_id = $ilUser->getId();
        }

        // Deal with commands
        switch ($cmd) {
            case "view":
                    $ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("crs_status_blocked"));
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
    public static function _checkGoto($target)
    {
        //$workflow = substr($params, 2, strpos($params,'EVT')-2);
        //$event = substr($params, strpos($params, 'EVT')+3);
        return true; // TODO Validate Event Syntax
    }
}
