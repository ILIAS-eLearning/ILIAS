<?php

require_once("classes/class.ilObjAICCLearningModuleGUI.php");

/**
* Class ilObjHACPLearningModuleGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
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

	/**
	* display dialogue for importing AICC package
	*
	* @access	public
	*/
	function importObject()
	{
		parent::importObject();
		$this->tpl->setVariable("FORMACTION", $this->getFormAction("save","adm_object.php?cmd=gateway&ref_id=".
			$_GET["ref_id"]."&new_type=hlm"));
		$this->tpl->setVariable("TXT_IMPORT_SLM", $this->lng->txt("import_hlm"));
	}


	/**
	* save new learning module to db
	*/
	function saveObject()
	{
		global $rbacadmin;

		$this->uploadObject();

		sendInfo($this->lng->txt("hlm_added"), true);
		ilUtil::redirect($this->getReturnLocation("save","adm_object.php?".$this->link_params));

	}

	

} // END class.ilObjAICCLearningModule
?>
