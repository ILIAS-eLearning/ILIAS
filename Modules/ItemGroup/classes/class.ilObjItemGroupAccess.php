<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Object/classes/class.ilObjectAccess.php';

/**
 * Item group access class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ModulesItemGroup
 */
class ilObjItemGroupAccess extends ilObjectAccess
{
	/**
	 * @var ilObjUser
	 */
	protected $user;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilRbacSystem
	 */
	protected $rbacsystem;

	/**
	 * @var ilAccessHandler
	 */
	protected $access;


	/**
	 * Constructor
	 */
	function __construct()
	{
		global $DIC;

		$this->user = $DIC->user();
		$this->lng = $DIC->language();
		$this->rbacsystem = $DIC->rbac()->system();
		$this->access = $DIC->access();
	}


	/**
	 * get list of command/permission combinations
	 *
	 * @access public
	 * @return array
	 * @static
	 */
	public static function _getCommands()
	{
		$commands = array
		(
			array("permission" => "read", "cmd" => "gotoParent", "lang_var" => "", "default" => true),
			array("permission" => "write", "cmd" => "listMaterials", "lang_var" => "edit_content", "default" => false),
			array("permission" => "write", "cmd" => "edit", "lang_var" => "settings", "default" => false)
		);
		
		return $commands;
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
		$ilUser = $this->user;
		$lng = $this->lng;
		$rbacsystem = $this->rbacsystem;
		$ilAccess = $this->access;
		
		$a_user_id = $a_user_id ? $a_user_id : $ilUser->getId();
		return true;
	}
	
	
	/**
	 * check whether goto script will succeed
	 */
	public static function _checkGoto($a_target)
	{
		global $DIC;

		$ilAccess = $DIC->access();
		
		$t_arr = explode("_", $a_target);

		if ($t_arr[0] != "itgr" || ((int) $t_arr[1]) <= 0)
		{
			return false;
		}

		if($ilAccess->checkAccess("read", "", $t_arr[1]))
		{
			return true;
		}
		return false;
	}
	
}
?>