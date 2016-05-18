<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/Scorm2004/classes/class.ilSCORM2004ScoGUI.php");
require_once("./Modules/Scorm2004/classes/class.ilSCORM2004Asset.php");

/**
 * Class ilSCORM2004AssetGUI
 *
 * User Interface for Scorm 2004 Asset Nodes
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ilCtrl_Calls ilSCORM2004AssetGUI: ilMDEditorGUI, ilNoteGUI, ilPCQuestionGUI, ilSCORM2004PageGUI
 *
 * @ingroup ModulesScorm2004
 */
class ilSCORM2004AssetGUI extends ilSCORM2004ScoGUI
{
	/**
	 * Constructor
	 * @access	public
	 */
	function __construct($a_slm_obj, $a_node_id = 0)
	{
		global $ilCtrl;

		$ilCtrl->saveParameter($this, "obj_id");
		$this->ctrl = $ilCtrl;

		parent::__construct($a_slm_obj, $a_node_id);
	}

	/**
	 * Get Node Type
	 */
	function getType()
	{
		return "ass";
	}

	/**
	 * Overwrite learning objectives editing function
	 */
	function showProperties()
	{
		return;
	}

	/**
	 * Overwrite learning objectives update function
	 */
	function updateProperties()
	{
		return;
	}

		/**
	 * output tabs
	 */
	function setTabs()
	{
		global $ilTabs, $ilCtrl, $tpl, $lng, $ilHelp;

		$ilHelp->setScreenIdComponent("sahsed");

		// subelements
		$ilTabs->addTarget("sahs_organization",
		$ilCtrl->getLinkTarget($this,'showOrganization'),
			 "showOrganization", get_class($this));

		// questions
		$ilTabs->addTarget("sahs_questions",
		$ilCtrl->getLinkTarget($this,'sahs_questions'),
			 "sahs_questions", get_class($this));

		// resources
		$ilTabs->addTarget("cont_files",
		$ilCtrl->getLinkTarget($this,'sco_resources'),
			 "sco_resources", get_class($this));

		// metadata
		$ilTabs->addTarget("meta_data",
		$ilCtrl->getLinkTargetByClass("ilmdeditorgui",''),
			 "", "ilmdeditorgui");

		// export
/*
		$ilTabs->addTarget("export",
		$ilCtrl->getLinkTarget($this, "showExportList"), "showExportList",
		get_class($this));

		// import
		$ilTabs->addTarget("import",
		$ilCtrl->getLinkTarget($this, "import"), "import",
		get_class($this));
*/
		// preview
		$ilTabs->addNonTabbedLink("preview",
			$lng->txt("cont_preview"),
			$ilCtrl->getLinkTarget($this,'sco_preview'), "_blank");

		$tpl->setTitleIcon(ilUtil::getImagePath("icon_ass.svg"));
		$tpl->setTitle(
		$lng->txt("obj_ass").": ".$this->node_object->getTitle());
	}

}
?>
