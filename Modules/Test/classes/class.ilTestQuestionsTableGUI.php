<?php
/*
        +-----------------------------------------------------------------------------+
        | ILIAS open source                                                           |
        +-----------------------------------------------------------------------------+
        | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
        |                                                                             |
        | This program is free software; you can redistribute it and/or               |
        | modify it under the terms of the GNU General Public License                 |
        | as published by the Free Software Foundation; either version 2              |
        | of the License, or (at your option) any later version.                      |
        |                                                                             |
        | This program is distributed in the hope that it will be useful,             |
        | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
        | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
        | GNU General Public License for more details.                                |
        |                                                                             |
        | You should have received a copy of the GNU General Public License           |
        | along with this program; if not, write to the Free Software                 |
        | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
        +-----------------------------------------------------------------------------+
*/

include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id: class.ilTestQuestionsTableGUI.php 20218 2009-06-15 22:14:10Z hschottm $
*
* @ingroup ModulesGroup
*/

class ilTestQuestionsTableGUI extends ilTable2GUI
{
	protected $writeAccess = false;
	protected $totalPoints = 0;
	protected $checked_move = false;
	protected $total = 0;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $a_write_access = false, $a_checked_move = false)
	{
		parent::__construct($a_parent_obj, $a_parent_cmd);

		global $lng, $ilCtrl;

		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
	
		$this->setWriteAccess($a_write_access);
		$this->setCheckedMove($a_checked_move);
		$this->setLimit(9999);
		
		$this->setFormName('questionbrowser');
		$this->setStyle('table', 'fullwidth');
		$this->addColumn('','f','1%');
		$this->addColumn($this->lng->txt("tst_question_title"),'title', '');
		$this->addColumn($this->lng->txt("tst_sequence"),'sequence', '');
		$this->addColumn($this->lng->txt("description"),'description', '');
		$this->addColumn($this->lng->txt("tst_question_type"),'type', '');
		$this->addColumn($this->lng->txt("points"),'', '');
		$this->addColumn($this->lng->txt("author"),'author', '');
		$this->addColumn($this->lng->txt("qpl"),'qpl', '');
	 	
		$this->setPrefix('q_id');
		$this->setSelectAllCheckbox('q_id');
		
		if ($this->getWriteAccess())
		{
			$this->addMultiCommand('removeQuestions', $this->lng->txt('remove_question'));
			$this->addMultiCommand('moveQuestions', $this->lng->txt('move'));
			if ($this->checked_move)
			{
				$this->addMultiCommand('insertQuestionsBefore', $this->lng->txt('insert_before'));
				$this->addMultiCommand('insertQuestionsAfter', $this->lng->txt('insert_after'));
			}
		}


		$this->setRowTemplate("tpl.il_as_tst_questions_row.html", "Modules/Test");

		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
		
		$this->disable('sort');
		$this->enable('header');
		$this->enable('select_all');
	}

	function fillHeader()
	{
		foreach ($this->column as $key => $column)
		{
			if (strcmp($column['text'], $this->lng->txt("points")) == 0)
			{
				$this->column[$key]['text'] = $this->lng->txt("points") . "&nbsp;(" . $this->totalPoints . ")";
			}
		}
		parent::fillHeader();
	}
	
	/**
	 * fill row 
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function fillRow($data)
	{
		global $ilUser,$ilAccess;

		$this->tpl->setVariable("QUESTION_ID", $data["question_id"]);
		if ($this->getWriteAccess() && !$this->getTotal()) 
		{
			$q_id = $data["question_id"];
			$qpl_ref_id = current(ilObject::_getAllReferences($data["obj_fi"]));
			$this->tpl->setVariable("QUESTION_TITLE", "<a href=\"" . $this->ctrl->getLinkTarget($this->getParentObject(), "questions") . "&eqid=$q_id&eqpl=$qpl_ref_id" . "\">" . $data["title"] . "</a>");
		} 
		else 
		{
			$this->tpl->setVariable("QUESTION_TITLE", $data["title"]);
		}
		$this->tpl->setVariable("QUESTION_SEQUENCE", $this->lng->txt("tst_sequence"));

		if ($this->getWriteAccess() && !$this->getTotal()) 
		{
			if ($data["sequence"] != 1)
			{
				$this->tpl->setVariable("BUTTON_UP", "<a href=\"" . $this->ctrl->getLinkTarget($this->getParentObject(), "questions") . "&up=".$data["question_id"]."\"><img src=\"" . ilUtil::getImagePath("a_up.gif") . "\" alt=\"" . $this->lng->txt("up") . "\" border=\"0\" /></a>");
			}
			if ($data["sequence"] != count($this->getData()))
			{
				$this->tpl->setVariable("BUTTON_DOWN", "<a href=\"" . $this->ctrl->getLinkTarget($this->getParentObject(), "questions") . "&down=".$data["question_id"]."\"><img src=\"" . ilUtil::getImagePath("a_down.gif") . "\" alt=\"" . $this->lng->txt("down") . "\" border=\"0\" /></a>");
			}
		}
		$this->tpl->setVariable("QUESTION_COMMENT", $data["description"]);
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		$this->tpl->setVariable("QUESTION_TYPE", assQuestion::_getQuestionTypeName($data["type_tag"]));
		$this->tpl->setVariable("QUESTION_POINTS", $data["points"]);
		$this->totalPoints += $data["points"];
		$this->tpl->setVariable("QUESTION_AUTHOR", $data["author"]);
		$this->tpl->setVariable("QUESTION_POOL", ilObject::_lookupTitle($data["obj_fi"]));
	}
	
	public function setWriteAccess($value)
	{
		$this->writeAccess = $value;
	}
	
	public function getWriteAccess()
	{
		return $this->writeAccess;
	}

	public function setCheckedMove($value)
	{
		$this->checked_move = $value;
	}
	
	public function getCheckedMove()
	{
		return $this->checked_move;
	}

	public function setTotal($value)
	{
		$this->total = $value;
	}
	
	public function getTotal()
	{
		return $this->total;
	}
}
?>