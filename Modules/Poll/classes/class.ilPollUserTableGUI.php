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
		$this->addColumn($lng->txt("lastname"), "lastname");
		$this->addColumn($lng->txt("firstname"), "firstname");
		
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
		
		$this->setExportFormats(array(self::EXPORT_CSV, self::EXPORT_EXCEL));
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
				$vote["answer".$answer_id] = in_array($answer_id, $answers);
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
			if($a_set["answer".$answer_id])
			{				
				$this->tpl->setVariable("ANSWER", '<img src="'.ilUtil::getImagePath("icon_ok.svg").'" />');
			}
			else
			{				
				$this->tpl->setVariable("ANSWER", "&nbsp;");				
			}
			$this->tpl->parseCurrentBlock();
		}	
		
		$this->tpl->setVariable("LOGIN", $a_set["login"]);		
		$this->tpl->setVariable("FIRSTNAME", $a_set["firstname"]);
		$this->tpl->setVariable("LASTNAME", $a_set["lastname"]);		
	}
	
	protected function fillRowCSV($a_csv, $a_set)
	{
		$a_csv->addColumn($a_set["login"]);		
		$a_csv->addColumn($a_set["lastname"]);
		$a_csv->addColumn($a_set["firstname"]);
		foreach($this->answer_ids as $answer_id)
		{			
			if($a_set["answer".$answer_id])
			{				
				$a_csv->addColumn(true);					
			}
			else
			{				
				$a_csv->addColumn(false);				
			}
		}	
		$a_csv->addRow();
	}
	
	protected function fillRowExcel($a_worksheet, &$a_row, $a_set)
	{
		$a_worksheet->write($a_row, 0, $a_set["login"]);		
		$a_worksheet->write($a_row, 1, $a_set["lastname"]);		
		$a_worksheet->write($a_row, 2, $a_set["firstname"]);
		$col = 2;
		foreach($this->answer_ids as $answer_id)
		{			
			if($a_set["answer".$answer_id])
			{				
				$a_worksheet->write($a_row, ++$col, true);						
			}
			else
			{				
				$a_worksheet->write($a_row, ++$col, false);				
			}
		}	
	}
}

?>