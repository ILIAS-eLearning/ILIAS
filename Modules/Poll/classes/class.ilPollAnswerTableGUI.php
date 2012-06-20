<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for poll answers
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
*
* @ingroup ModulesPoll
*/
class ilPollAnswerTableGUI extends ilTable2GUI
{
	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd)
	{
		global $ilCtrl, $lng;
		
		$this->setId("ilobjpollaw");
		
		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->addColumn("", "", "1", true);
		$this->addColumn($lng->txt("poll_sortorder"), "pos");
		$this->addColumn($lng->txt("poll_answer"), "answer");
		$this->addColumn($lng->txt("poll_percentage"), "percentage");
		$this->addColumn($lng->txt("action"));
	
		$this->setTitle($this->lng->txt("poll_answers"));
		$this->setDescription($this->lng->txt("poll_question").": \"".
			$a_parent_obj->object->getQuestion()."\"");

		// $this->setSelectAllCheckbox("item_id");
		$this->addMultiCommand("confirmDeleteAnswers", $lng->txt("delete"));
		$this->addCommandButton("updateAnswerOrder", $lng->txt("poll_update_order"));
		
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setRowTemplate("tpl.answer_row.html", "Modules/Poll");		
		$this->setDefaultOrderField("pos");
		$this->setDefaultOrderDirection("asc");
		
		$this->getItems();		
	}
	
	public function numericOrdering($a_field) 
	{
		if($a_field != "answer")
		{
			return true;
		}
		return false;
	}

	function getItems()
	{
		$data = $this->parent_obj->object->getAnswers();
		$perc = $this->parent_obj->object->getVotePercentages();
		
		// add current percentages
		foreach($data as $idx => $item)
		{
			if(!isset($perc[$item["id"]]))
			{
				$data[$idx]["percentage"] = 0;
			}
			else
			{
				$data[$idx]["percentage"] = number_format($perc[$item["id"]]["perc"], 2);
			}
		}

		$this->setData($data);		
	}
	
	protected function fillRow($a_set)
	{
		global $ilCtrl, $lng;
		
		$this->tpl->setVariable("ID", $a_set["id"]);
		$this->tpl->setVariable("VALUE_POS", $a_set["pos"]);
		$this->tpl->setVariable("TXT_ANSWER", nl2br($a_set["answer"]));
		
		$this->tpl->setVariable("VALUE_PERCENTAGE", $a_set["percentage"]);
		
		$ilCtrl->setParameter($this->parent_obj, "pa_id", $a_set["id"]);
		$url = $ilCtrl->getLinkTarget($this->parent_obj, "editAnswer");
		$ilCtrl->setParameter($this->parent_obj, "pa_id", "");
	
		$this->tpl->setVariable("URL_EDIT", $url);					
		$this->tpl->setVariable("TXT_EDIT", $lng->txt("edit"));	
	}
}

?>