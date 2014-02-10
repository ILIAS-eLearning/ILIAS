<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for poll users
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
*
* @ingroup ModulesPoll
*/
class ilPollUserTableGUI extends ilTable2GUI
{
	protected $answer_ids; // [array]
	
	function __construct($a_parent_obj, $a_parent_cmd)
	{
		global $ilCtrl, $lng;
		
		$this->setId("ilobjpollusr");
		
		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->addColumn($lng->txt("login"), "login");
		$this->addColumn($lng->txt("lastname"), "firstname");
		$this->addColumn($lng->txt("firstname"), "lastname");
		
		foreach($this->getParentObject()->object->getAnswers() as $answer)
		{
			$this->answer_ids[] = $answer["id"];
			$this->addColumn($answer["answer"], "answer".$answer["id"]);
		}
				
		$this->getItems($this->answer_ids);		
		
		$this->setTitle($this->lng->txt("poll_question").": \"".
			$this->getParentObject()->object->getQuestion()."\"");
	
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setRowTemplate("tpl.user_row.html", "Modules/Poll");		
		$this->setDefaultOrderField("login");
		$this->setDefaultOrderDirection("asc");				
	}	
	
	protected function getItems(array $a_answer_ids)
	{		
		$data = array();
		
		foreach($this->getParentObject()->object->getVotesByUsers() as $user_id => $vote)
		{
			$answers = $vote["answers"];
			unset($vote["answers"]);
			
			foreach($a_answer_ids as $answer_id)
			{
				$status = "";
				if(in_array($answer_id, $answers))
				{
					$status = "x";
				}
				$vote["answer".$answer_id] = $status;
			}
			
			$data[] = $vote;
		}
		
		$this->setData($data);		
	}	
	
	protected function fillRow($a_set)
	{				
		$this->tpl->setCurrentBlock("answer_bl");
		foreach($this->answer_ids as $answer_id)
		{			
			$this->tpl->setVariable("ANSWER", $a_set["answer".$answer_id]);
			$this->tpl->parseCurrentBlock();
		}	
		
		$this->tpl->setVariable("LOGIN", $a_set["login"]);		
		$this->tpl->setVariable("FIRSTNAME", $a_set["firstname"]);
		$this->tpl->setVariable("LASTNAME", $a_set["lastname"]);		
	}
}

?>