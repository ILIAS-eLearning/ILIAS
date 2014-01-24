<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Handles user interface for exercises
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilExerciseHandlerGUI: ilObjExerciseGUI
*
* @ingroup ModulesExercise
*/
class ilExerciseHandlerGUI
{
	function ilExerciseHandlerGUI()
	{
		global $ilCtrl, $lng, $ilAccess, $ilias, $ilNavigationHistory;

		// initialisation stuff
		$this->ctrl =&  $ilCtrl;
		
		//$ilNavigationHistory->addItem($_GET["ref_id"],
		//	"ilias.php?baseClass=ilGlossaryEditorGUI&ref_id=".$_GET["ref_id"]);

	}
	
	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $lng, $ilAccess, $tpl, $ilNavigationHistory;
		
		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass($this);
		if ($next_class == "")
		{
			$this->ctrl->setCmdClass("ilobjexercisegui");
			$next_class = $this->ctrl->getNextClass($this);
		}

		// add entry to navigation history
		if ($ilAccess->checkAccess("read", "", $_GET["ref_id"]))
		{
			$ilNavigationHistory->addItem($_GET["ref_id"],
				"ilias.php?baseClass=ilExerciseHandlerGUI&cmd=showOverview&ref_id=".$_GET["ref_id"], "exc");
		}

		switch ($next_class)
		{
			case 'ilobjexercisegui':
				require_once "./Modules/Exercise/classes/class.ilObjExerciseGUI.php";
				$ex_gui =& new ilObjExerciseGUI("", (int) $_GET["ref_id"], true, false);
				$this->ctrl->forwardCommand($ex_gui);
				break;
		}

		$tpl->show();
	}

}
?>