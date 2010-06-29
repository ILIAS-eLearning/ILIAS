<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");
include_once("./Modules/TestQuestionPool/classes/class.assQuestion.php");
include_once("./Modules/LearningModule/classes/class.ilLMObject.php");

/**
 * Question list table
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ModulesLearningModle
 */
class ilLMQuestionListTableGUI extends ilTable2GUI
{

	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd, $a_lm)
	{
		global $ilCtrl, $lng, $ilAccess, $lng, $rbacsystem;

		$this->lm = $a_lm;

		$this->setId("lm_qst".$this->lm->getId());

		parent::__construct($a_parent_obj, $a_parent_cmd);
//		$this->setTitle($this->lng->txt("users"));

		//$this->addColumn("", "", "1", true);
		$this->addColumn($this->lng->txt("pg"));
		$this->addColumn($this->lng->txt("question"));
		$this->addColumn($this->lng->txt("cont_users_answered"));
		$this->addColumn($this->lng->txt("cont_correct_after_first"));
		$this->addColumn($this->lng->txt("cont_second"));
		$this->addColumn($this->lng->txt("cont_third_and_more"));
		$this->addColumn($this->lng->txt("cont_never"));

		$this->setExternalSorting(true);
		$this->setExternalSegmentation(true);
		$this->setEnableHeader(true);
		//$this->setFormAction($ilCtrl->getFormAction($this->parent_obj, "applyFilter"));
		$this->setFormAction($ilCtrl->getFormAction($this->parent_obj, $this->parent_cmd));
		$this->setRowTemplate("tpl.lm_question_row.html", "Modules/LearningModule");
		//$this->disable("footer");
		$this->setEnableTitle(true);
//		$this->initFilter();
//		$this->setFilterCommand("applyFilter");
//		$this->setDefaultOrderField("login");
//		$this->setDefaultOrderDirection("asc");

//		$this->setSelectAllCheckbox("id[]");

//		$this->addMultiCommand("activateUsers", $lng->txt("activate"));

		$this->getItems();
	}

	/**
	* Get user items
	*/
	function getItems()
	{
		global $lng;
//if ($GLOBALS["kk"]++ == 1) nj();

		$this->determineOffsetAndOrder();

		include_once("./Modules/LearningModule/classes/class.ilLMPageObject.php");

		$questions = ilLMPageObject::queryQuestionsOfLearningModule(
			$this->lm->getId(),
			ilUtil::stripSlashes($this->getOrderField()),
			ilUtil::stripSlashes($this->getOrderDirection()),
			ilUtil::stripSlashes($this->getOffset()),
			ilUtil::stripSlashes($this->getLimit())
			);

		if (count($questions["set"]) == 0 && $this->getOffset() > 0)
		{
			$this->resetOffset();
			$questions = ilLMPageObject::queryQuestionsOfLearningModule(
				$this->lm->getId(),
				ilUtil::stripSlashes($this->getOrderField()),
				ilUtil::stripSlashes($this->getOrderDirection()),
				ilUtil::stripSlashes($this->getOffset()),
				ilUtil::stripSlashes($this->getLimit())
				);
		}

		$this->setMaxCount($questions["cnt"]);
		$this->setData($questions["set"]);
	}


	/**
	* Fill table row
	*/
	protected function fillRow($a_set)
	{
		global $ilCtrl, $lng;

		$this->tpl->setVariable("PAGE_TITLE",
			ilLMObject::_lookupTitle($a_set["page_id"]));
		$this->tpl->setVariable("QUESTION",
			assQuestion::_getQuestionText($a_set["question_id"]));

		include_once("./Services/COPage/classes/class.ilPageQuestionProcessor.php");
		$stats = ilPageQuestionProcessor::getQuestionStatistics($a_set["question_id"]);

		$this->tpl->setVariable("VAL_ANSWERED", (int) $stats["all"]);
		if ($stats["all"] == 0)
		{
			$this->tpl->setVariable("VAL_CORRECT_FIRST", 0);
			$this->tpl->setVariable("VAL_CORRECT_SECOND", 0);
			$this->tpl->setVariable("VAL_CORRECT_THIRD_OR_MORE", 0);
			$this->tpl->setVariable("VAL_NEVER", 0);
		}
		else
		{
			$this->tpl->setVariable("VAL_CORRECT_FIRST", $stats["first"].
				" (".(100/$stats["all"] * $stats["first"])." %)");
			$this->tpl->setVariable("VAL_CORRECT_SECOND", $stats["second"].
				" (".(100/$stats["all"] * $stats["second"])." %)");
			$this->tpl->setVariable("VAL_CORRECT_THIRD_AND_MORE", $stats["third_or_more"].
				" (".(100/$stats["all"] * $stats["third_or_more"])." %)");
			$nev = $stats["all"] - $stats["first"] - $stats["second"] - $stats["third_or_more"];
			$this->tpl->setVariable("VAL_NEVER", $nev.
				" (".(100/$stats["all"] * $nev)." %)");
		}
	}
}

?>
