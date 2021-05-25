<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 *
 *
 * @author Alex Killing <killing@leifos.de>
 */
class ilNotificationAppEventListener implements ilAppEventListener
{
    /**
     * Handle events like create, update, delete
     *
     * @access public
     * @param	string	$a_component	component, e.g. "Modules/Forum" or "Services/User"
     * @param	string	$a_event		event e.g. "createUser", "updateUser", "deleteUser", ...
     * @param	array	$a_parameter	parameter array (assoc), array("name" => ..., "phone_office" => ...)	 *
     * @static
     */
    public static function handleEvent($a_component, $a_event, $a_parameter)
    {
        switch ($a_component) {
            case 'Services/Object':

                switch ($a_event) {
                    case 'delete':
                        if ($a_parameter['obj_id'] > 0) {
                            $set = new ilObjNotificationSettings($a_parameter['obj_id']);
                            $set->delete();
                            break;
                        }
                }
                break;
        }
    }
}
