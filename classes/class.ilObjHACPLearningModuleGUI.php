<?php

require_once("classes/class.ilObjAICCLearningModuleGUI.php");

/**
* Class ilObjHACPLearningModuleGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @ilCtrl_Calls ilObjHACPLearningModuleGUI: ilFileSystemGUI
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
		$this->type = "hlm";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output);
		$this->tabs_gui =& new ilTabsGUI();

	}
	
	function getNewObject() {
		// create  object in objecttree
		include_once("classes/class.ilObjHACPLearningModule.php");
		return new ilObjHACPLearningModule();
	}

} // END class.ilObjAICCLearningModule
?>
