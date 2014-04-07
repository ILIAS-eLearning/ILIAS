<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/Object/classes/class.ilObjectAccess.php";
include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

/**
* Class ilObjQuestionPoolAccess
*
*
* @author		Helmut Schottmueller <helmut.schottmueller@mac.com>
* @author 		Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup ModulesTestQuestionPool
*/
class ilObjQuestionPoolAccess extends ilObjectAccess
{
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
			array("permission" => "write", "cmd" => "questions", "lang_var" => "tst_edit_questions"),
			array("permission" => "write", "cmd" => "ilObjQuestionPoolSettingsGeneralGUI::showForm", "lang_var" => "settings"),
			#array("permission" => "write", "cmd" => "questions", "lang_var" => "edit",
			#	"default" => false),
			array("permission" => "read", "cmd" => "questions", "lang_var" => "edit",
				"default" => true)
		);
		
		return $commands;
	}
}

?>
