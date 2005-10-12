<?php

require_once("content/classes/class.ilObjAICCLearningModuleGUI.php");
require_once("content/classes/class.ilObjHACPLearningModule.php");

/**
* Class ilObjHACPLearningModuleGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @ilCtrl_Calls ilObjHACPLearningModuleGUI: ilFileSystemGUI, ilMDEditorGUI
*
* @extends ilObjectGUI
* @package ilias-core
*/
class ilObjHACPLearningModuleGUI extends ilObjAICCLearningModuleGUI
{
	/**
	* Constructor
	*
	* @access	public
	*/
	function ilObjHACPLearningModuleGUI($a_data,$a_id,$a_call_by_reference, $a_prepare_output = true)
	{
		global $lng;

		$lng->loadLanguageModule("content");
		$this->type = "sahs";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output);
		$this->tabs_gui =& new ilTabsGUI();

	}


	/**
	* assign hacp object to hacp gui object
	*/
	function assignObject()
	{
		if ($this->id != 0)
		{
			if ($this->call_by_reference)
			{
				$this->object =& new ilObjHACPLearningModule($this->id, true);
			}
			else
			{
				$this->object =& new ilObjHACPLearningModule($this->id, false);
			}
		}
	}

	

} // END class.ilObjAICCLearningModule
?>
