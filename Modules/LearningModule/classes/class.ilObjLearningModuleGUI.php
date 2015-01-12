<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/LearningModule/classes/class.ilObjContentObjectGUI.php");

/**
* Class ilLearningModuleGUI
*
* GUI class for ilLearningModule
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ilCtrl_Calls ilObjLearningModuleGUI: ilLMPageObjectGUI, ilStructureObjectGUI, ilObjStyleSheetGUI, ilMDEditorGUI
* @ilCtrl_Calls ilObjLearningModuleGUI: ilLearningProgressGUI, ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
* @ilCtrl_Calls ilObjLearningModuleGUI: ilExportGUI, ilCommonActionDispatcherGUI, ilPageMultiLangGUI, ilObjectTranslationGUI
*
* @ingroup ModulesIliasLearningModule
*/
class ilObjLearningModuleGUI extends ilObjContentObjectGUI
{
	var $object;
	/**
	* Constructor
	* @access	public
	*/
	function ilObjLearningModuleGUI($a_data,$a_id = 0,$a_call_by_reference = true, $a_prepare_output = true)
	{
		$this->type = "lm";

		parent::ilObjContentObjectGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

		# BETTER DO IT HERE THAN IN PARENT CLASS ( PROBLEMS FOR import, create)
		$this->assignObject();

		// SAME REASON
		if($a_id != 0)
		{
			$this->lm_tree =& $this->object->getLMTree();
		}
		/*
		global $ilias, $tpl, $lng, $objDefinition;

		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->objDefinition =& $objDefinition;
		$this->lm_tree =& $a_tree;
		*/

		//$this->read(); todo
	}

	function assignObject()
	{
		include_once("./Modules/LearningModule/classes/class.ilObjLearningModule.php");

		$this->link_params = "ref_id=".$this->ref_id;
		$this->object =& new ilObjLearningModule($this->id, true);
	}

	/*
	function setLearningModuleObject(&$a_lm_obj)
	{
		$this->lm_obj =& $a_lm_obj;
		//$this->obj =& $this->lm_obj;
	}*/

	// MOVED ALL *style METHODS TO base class

	function view()
	{
		if (strtolower($_GET["baseClass"]) == "iladministrationgui")
		{
			$this->prepareOutput();
			parent::viewObject();
		}
		else
		{
			$this->properties();
		}
	}

}

?>
