<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for results by question
 * @author  Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @version $Id$
 * @ingroup ModulesTest
 */
class ilResultsByQuestionTableGUI extends ilTable2GUI
{
	protected $has_pdf;

	function ilResultsByQuestionTableGUI($a_parent_obj, $a_parent_cmd = "", $has_pdf = false)
	{
		global $ilCtrl, $lng;

		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->has_pdf = $has_pdf;
		$this->addColumn($lng->txt("question_id"), "qid", "");
		$this->addColumn($lng->txt("question_title"), "question_title", "35%");
		$this->addColumn($lng->txt("number_of_answers"), "number_of_answers", "15%");
		if($has_pdf)
		{
			$this->addColumn($lng->txt("output"), "", "20%");
			$this->addColumn($lng->txt("file_uploads"), "", "20%");
		}
		else
		{
			$this->addColumn($lng->txt("file_uploads"), "", "40%");
		}
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.table_results_by_question_row.html", "Modules/Test");
		$this->setDefaultOrderField("question_title");
		$this->setDefaultOrderDirection("asc");
	}

	/**
	 * Standard Version of Fill Row. Most likely to
	 * be overwritten by derived class.
	 */
	protected function fillRow($a_set)
	{
		if($this->has_pdf)
		{
			$this->tpl->setCurrentBlock('pdf');
			$this->tpl->setVariable("PDF_EXPORT", $a_set[3]);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setVariable("QUESTION_ID", $a_set[0]);
		$this->tpl->setVariable("QUESTION_TITLE", $a_set[1]);
		$this->tpl->setVariable("NUMBER_OF_ANSWERS", $a_set[2]);
		$this->tpl->setVariable("FILE_UPLOADS", $a_set[4]);
	}

	/**
	 * @param string $a_field
	 * @return bool
	 */
	public function numericOrdering($a_field)
	{
		switch($a_field)
		{
			case 'qid':
				return true;

			default:
				return false;
		}
	}
}