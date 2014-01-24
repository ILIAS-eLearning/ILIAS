<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");
include_once("./Services/Help/classes/class.ilHelpMapping.php");


/**
 * Help mapping
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilHelpMappingTableGUI extends ilTable2GUI
{
	var $online_help_mode = false;
	
	
	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_validation = false)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;

		$this->setId("lm_help_map");
		$this->validation = $a_validation;

		parent::__construct($a_parent_obj, $a_parent_cmd);
		include_once("./Modules/LearningModule/classes/class.ilLMPageObject.php");
		
		$this->getChapters();

		$this->setTitle($lng->txt("help_assign_help_ids"));

		$this->addColumn($this->lng->txt("st"), "title");
		$this->addColumn($this->lng->txt("cont_screen_ids"));

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.help_map_row.html", "Modules/LearningModule");
		$this->setDefaultOrderField("title");
		$this->setDefaultOrderDirection("asc");

		$this->addCommandButton("saveHelpMapping", $lng->txt("save"));
	}
	
	/**
	 * Get chapters
	 *
	 * @param
	 * @return
	 */
	function getChapters()
	{
		$hc = ilSession::get("help_chap");
		$lm_tree = $this->parent_obj->object->getTree();
		
		if ($hc > 0 && $lm_tree->isInTree($hc))
		{
			//$node = $lm_tree->getNodeData($hc);
			//$chaps = $lm_tree->getSubTree($node);
			$chaps = $lm_tree->getFilteredSubTree($hc, array("pg"));
			unset($chaps[0]);
		}
		else
		{
			$chaps = ilStructureObject::getChapterList($this->parent_obj->object->getId());
		}
		
		$this->setData($chaps);
	}
	

	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng;

		$this->tpl->setVariable("VAL_TITLE", $a_set["title"]);
		$this->tpl->setVariable("PAGE_ID", $a_set["obj_id"]);

		$screen_ids = ilHelpMapping::getScreenIdsOfChapter($a_set["obj_id"]);

		$this->tpl->setVariable("SCREEN_IDS",
			ilUtil::prepareFormOutput(implode($screen_ids, "\n")));
		
	}

}

?>