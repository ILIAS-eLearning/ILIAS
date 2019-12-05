<?php

class ilObjStudyProgrammeReferenceAccess extends ilContainerReferenceAccess
{
	/**
	* Checks wether a user may invoke a command or not
	* (this method is called by ilAccessHandler::checkAccess)
	*
	* Please do not check any preconditions handled by
	* ilConditionHandler here. Also don't do any RBAC checks.
	*
	* @global ilAccessHandler $ilAccess 
	*
	* @param	string		$a_cmd			command (not permission!)
 	* @param	string		$a_permission	permission
	* @param	int			$a_ref_id		reference id
	* @param	int			$a_obj_id		object id
	* @param	int			$a_user_id		user id (if not provided, current user is taken)
	*
	* @return	boolean		true, if everything is ok
	*/
	function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "")
	{
		global $DIC;

		$ilAccess = $DIC['ilAccess'];
		
		switch($a_permission)
		{
			case 'visible':
			case 'read':
				$target_ref_id = ilContainerReference::_lookupTargetRefId($a_obj_id);
				if(!$ilAccess->checkAccessOfUser($a_user_id, $a_permission, $a_cmd, $target_ref_id))
				{
					return false;
				}
				break;
			case "delete":
				$target_ref_id = ilContainerReference::_lookupTargetRefId($a_obj_id);
				$prg = ilObjStudyProgramme::getInstanceByRefId($a_ref_id);
				if ($prg->hasRelevantProgresses() || !$ilAccess->checkAccessOfUser($a_user_id, $a_permission, $a_cmd, $a_ref_id)) {
					return false;
				}
				break;
		}

		return true;
	}

	static function _getCommands($a_ref_id = null)
	{
		global $DIC;

		$ilAccess = $DIC->access();

		if($ilAccess->checkAccess('write','',$a_ref_id)) {
			// Only local (reference specific commands)
			$commands = [
				["permission" => "read", "cmd" => "view", "lang_var" => "show", "default" => true]
				,["permission" => "write", "cmd" => "view", "lang_var" => "edit_content"]
				,["permission" => "write", "cmd" => "edit", "lang_var" => "settings"]
				,["permission" => "write", "cmd" => "editReference", "lang_var" => "edit"]
			];
		}
		else {
			$commands = ilObjStudyProgrammeAccess::_getCommands();
		}
		return $commands;
	}
	
}