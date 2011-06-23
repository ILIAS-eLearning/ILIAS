<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

include_once("./Services/Skill/classes/class.ilSkillTreeNode.php");

/**
 * Self evaluation overview table
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilSelfEvaluationTableGUI extends ilTable2GUI
{
	
	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd)
	{
		global $ilCtrl, $lng, $ilAccess, $lng, $ilUser;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		include_once("./Services/Skill/classes/class.ilSkillSelfEvaluation.php");
		$this->setData(ilSkillSelfEvaluation::getAllSelfEvaluationsOfUser($ilUser->getId()));
		$this->setTitle($lng->txt("skmg_self_evaluations"));

		$this->addColumn("", "", 1);
		$this->addColumn($this->lng->txt("created"));
		$this->addColumn($this->lng->txt("last_update"));
		$this->addColumn($this->lng->txt("skmg_skill"));
		$this->addColumn($this->lng->txt("actions"));
		
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.self_eval_overview_row.html", "Services/Skill");
		$this->setEnableTitle(true);
		
		$this->addMultiCommand("confirmSelfEvaluationDeletion", $lng->txt("delete"));
		//$this->addCommandButton("", $lng->txt(""));
	}
	
	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl;

		$this->tpl->setVariable("SE_ID", $a_set["id"]);
		$this->tpl->setVariable("VAL_CREATED", $a_set["created"]);
		$this->tpl->setVariable("VAL_LAST_UPDATE", $a_set["last_update"]);
		$this->tpl->setVariable("VAL_SKILL",
			ilSkillTreeNode::_lookupTitle($a_set["top_skill_id"]));
		$this->tpl->setVariable("TXT_CMD", $lng->txt("edit"));
		$ilCtrl->setParameter($this->parent_obj, "se_id", $a_set["id"]);
		$this->tpl->setVariable("HREF_CMD",
			$ilCtrl->getLinkTarget($this->parent_obj, "editSelfEvaluation"));
	}
	
}
?>
