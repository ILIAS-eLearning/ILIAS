<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObjectAccess.php");

/**
 * Class ilObjLinkResourceAccess
 *
 *
 * @author 		Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ModulesWebResource
 */
class ilObjLinkResourceAccess extends ilObjectAccess
{
    public static $item = array();
    public static $single_link = array();
    
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
            array("permission" => "read", "cmd" => "", "lang_var" => "show",
                "default" => true),
            array("permission" => "read", "cmd" => "exportHTML", "lang_var" => "export_html"),
            array("permission" => "write", "cmd" => "editLinks", "lang_var" => "edit_content"),
            array("permission" => "write", "cmd" => "settings", "lang_var" => "settings")
        );
        
        return $commands;
    }

    /**
    * check whether goto script will succeed
    */
    public static function _checkGoto($a_target)
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        
        $t_arr = explode("_", $a_target);

        if ($t_arr[0] != "webr" || ((int) $t_arr[1]) <= 0) {
            return false;
        }

        if ($ilAccess->checkAccess("read", "", $t_arr[1]) ||
            $ilAccess->checkAccess("visible", "", $t_arr[1])) {
            return true;
        }
        return false;
    }

    /**
     * checks wether a user may invoke a command or not
     * (this method is called by ilAccessHandler::checkAccess)
     *
     * @param	string		$a_cmd		command (not permission!)
     * @param	string		$a_permission	permission
     * @param	int			$a_ref_id	reference id
     * @param	int			$a_obj_id	object id
     * @param	int			$a_user_id	user id (if not provided, current user is taken)
     *
     * @return	boolean		true, if everything is ok
     */
    public function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "")
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        
        // Set offline if no valid link exists
        if ($a_permission == 'read') {
            if (!self::_getFirstLink($a_obj_id) && !$rbacsystem->checkAccessOfUser($a_user_id, 'write', $a_ref_id)) {
                return false;
            }
        }
            
        if ($a_cmd == "settings") {
            if (self::_checkDirectLink($a_obj_id)) {
                return false;
            }
        }
        return parent::_checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id);
    }

    /**
     * Get first link item
     * Check before with _isSingular() if there is more or less than one
     *
     * @param	int			$a_webr_id		object id of web resource
     * @return array link item data
     *
     */
    public static function _getFirstLink($a_webr_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (isset(self::$item[$a_webr_id])) {
            return self::$item[$a_webr_id];
        }
        $query = "SELECT * FROM webr_items " .
            "WHERE webr_id = " . $ilDB->quote($a_webr_id, 'integer') . ' ' .
            "AND active = " . $ilDB->quote(1, 'integer') . ' ';
        $res = $ilDB->query($query);
        
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $item['title']				= $row->title;
            $item['description']		= $row->description;
            $item['target']				= $row->target;
            $item['active']				= (bool) $row->active;
            $item['disable_check']		= $row->disable_check;
            $item['create_date']		= $row->create_date;
            $item['last_update']		= $row->last_update;
            $item['last_check']			= $row->last_check;
            $item['valid']				= $row->valid;
            $item['link_id']			= $row->link_id;
            self::$item[$row->webr_id] = $item;
        }
        return $item ? $item : array();
    }

    /**
     * Preload data
     *
     * @param array $a_obj_ids array of object ids
     */
    public static function _preloadData($a_obj_ids, $a_ref_ids)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];
        
        $res = $ilDB->query(
            "SELECT * FROM webr_items WHERE " .
                $ilDB->in("webr_id", $a_obj_ids, false, "integer") .
                " AND active = " . $ilDB->quote(1, 'integer')
        );
        foreach ($a_obj_ids as $id) {
            self::$item[$id] = array();
        }
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $item['title']				= $row->title;
            $item['description']		= $row->description;
            $item['target']				= $row->target;
            $item['active']				= (bool) $row->active;
            $item['disable_check']		= $row->disable_check;
            $item['create_date']		= $row->create_date;
            $item['last_update']		= $row->last_update;
            $item['last_check']			= $row->last_check;
            $item['valid']				= $row->valid;
            $item['link_id']			= $row->link_id;
            self::$item[$row->webr_id] = $item;
        }
    }

    /**
     * Check whether there is only one active link in the web resource.
     * In this case this link is shown in a new browser window
     *
     */
    public static function _checkDirectLink($a_obj_id)
    {
        if (isset(self::$single_link[$a_obj_id])) {
            return self::$single_link[$a_obj_id];
        }
        include_once './Modules/WebResource/classes/class.ilLinkResourceItems.php';
        return self::$single_link[$a_obj_id] = ilLinkResourceItems::_isSingular($a_obj_id);
    }
}
