<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Modules/LearningModule/classes/class.ilObjContentObjectAccess.php");
include_once './Services/AccessControl/interfaces/interface.ilConditionHandling.php';

/**
* Class ilObjLearningModuleAccess
*
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesIliasLearningModule
*/
class ilObjLearningModuleAccess extends ilObjContentObjectAccess implements ilConditionHandling
{
	/**
	 * Get possible conditions operators
	 */
	public static function getConditionOperators()
	{
		// currently only one mode "ilConditionHandler::OPERATOR_LP" 
		// which is automatically added by condition handling, if lp is activated
		return array();
	}
	
	
	/**
	 * check condition
	 * @param type $a_svy_id
	 * @param type $a_operator
	 * @param type $a_value
	 * @param type $a_usr_id
	 * @return boolean
	 */
	public static function checkCondition($a_trigger_obj_id,$a_operator,$a_value,$a_usr_id)
	{
		return TRUE;
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
	function _getCommands()
	{
		$commands = array
		(
			array("permission" => "read", "cmd" => "view", "lang_var" => "show",
				"default" => true),
			array("permission" => "read", "cmd" => "continue", "lang_var" => "continue_work"),
			array("permission" => "write", "cmd" => "edit", "lang_var" => "edit_content"),
			array("permission" => "write", "cmd" => "properties", "lang_var" => "settings")
		);
		
		return $commands;
	}
	
}

?>
