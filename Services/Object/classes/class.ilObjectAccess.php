<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class ilObjectAccess
*
* This class contains methods that check object specific conditions
* for access to objects. Every object type should provide an
* inherited class called ilObj<TypeName>Access
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*/
class ilObjectAccess implements \ilWACCheckingClass
{
    /**
    * Checks wether a user may invoke a command or not
    * (this method is called by ilAccessHandler::checkAccess)
    *
    * Please do not check any preconditions handled by
    * ilConditionHandler here. Also don't do any RBAC checks.
    *
    * @param	string		$a_cmd			command (not permission!)
    * @param	string		$a_permission	permission
    * @param	int			$a_ref_id		reference id
    * @param	int			$a_obj_id		object id
    * @param	int			$a_user_id		user id (if not provided, current user is taken)
    *
    * @return	boolean		true, if everything is ok
    */
    public function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "")
    {

        // add no access info item and return false if access is not granted
        // $ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $a_text, $a_data = "");
        //
        // for all RBAC checks use checkAccessOfUser instead the normal checkAccess-method:
        // $rbacsystem->checkAccessOfUser($a_user_id, $a_permission, $a_ref_id)

        return true;
    }

    /**
    * check condition
    *
    * this method is called by ilConditionHandler
    */
    public function _checkCondition($a_obj_id, $a_operator, $a_value, $a_usr_id)
    {
        switch ($a_operator) {
            default:
                return true;
        }
    }
    
    /**
     * get commands
     *
     * this method returns an array of all possible commands/permission combinations
     *
     * example:
     * $commands = array
     *	(
     *		array("permission" => "read", "cmd" => "view", "lang_var" => "show"),
     *		array("permission" => "write", "cmd" => "edit", "lang_var" => "edit"),
     *	);
     */
    public static function _getCommands()
    {
        $commands = array(
            array()
        );
        
        return $commands;
    }
    
    /**
    * check whether goto script will succeed
    */
    public static function _checkGoto($a_target)
    {
        global $DIC;

        $ilAccess = $DIC->access();
        
        $t_arr = explode("_", $a_target);

        if ($ilAccess->checkAccess("read", "", $t_arr[1])) {
            return true;
        }
        return false;
    }

    /**
     * Type-specific implementation of general status, has to be overwritten if object type does
     * not support centralized offline handling
     *
     * Used in ListGUI and Learning Progress
     *
     * @param int $a_obj_id
     * @return bool
     */
    public static function _isOffline($a_obj_id)
    {
        global $DIC;

        $objDefinition = $DIC['objDefinition'];
        if ($objDefinition->supportsOfflineHandling(ilObject::_lookupType($a_obj_id))) {
            return ilObject::lookupOfflineStatus($a_obj_id);
        }
        return null;
    }

    /**
     * Preload data
     *
     * @param array $a_obj_ids array of object ids
     */
    public static function _preloadData($a_obj_ids, $a_ref_ids)
    {
    }

    /**
     * @inheritdoc
     */
    public function canBeDelivered(ilWACPath $ilWACPath)
    {
        global $ilAccess;

        preg_match("/\\/obj_([\\d]*)\\//uism", $ilWACPath->getPath(), $results);
        foreach (ilObject2::_getAllReferences($results[1]) as $ref_id) {
            if ($ilAccess->checkAccess('visible', '', $ref_id) || $ilAccess->checkAccess('read', '', $ref_id)) {
                return true;
            }
        }

        return false;
    }
}
