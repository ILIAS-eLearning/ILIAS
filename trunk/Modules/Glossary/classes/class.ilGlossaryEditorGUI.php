<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilGlossaryEditorGUI
*
* GUI class for Glossary Editor
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilGlossaryEditorGUI: ilObjGlossaryGUI
*
* @ingroup ModulesGlossary
*/
class ilGlossaryEditorGUI
{
	function ilGlossaryEditorGUI()
	{
		global $ilCtrl, $lng, $ilAccess, $ilias, $ilNavigationHistory;
		
		// initialisation stuff
		$this->ctrl =&  $ilCtrl;
		$lng->loadLanguageModule("content");
		
		// check write permission
		if (!$ilAccess->checkAccess("write", "", $_GET["ref_id"]) &&
			!$ilAccess->checkAccess("edit_content", "", $_GET["ref_id"]))
		{
			$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->MESSAGE);
		}
		
		$ilNavigationHistory->addItem($_GET["ref_id"],
			"ilias.php?baseClass=ilGlossaryEditorGUI&ref_id=".$_GET["ref_id"],
			"glo");

	}
	
	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $lng, $ilAccess;
		
		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass($this);
		if ($next_class == "")
		{
			$this->ctrl->setCmdClass("ilobjglossarygui");
			$this->ctrl->setCmd("");
		}

		switch ($next_class)
		{
			case 'ilobjglossarygui':
			default:
				require_once "./Modules/Glossary/classes/class.ilObjGlossaryGUI.php";
				$glossary_gui =& new ilObjGlossaryGUI("", $_GET["ref_id"], true, false);
				$this->ctrl->forwardCommand($glossary_gui);
				break;
		}
	}

}
