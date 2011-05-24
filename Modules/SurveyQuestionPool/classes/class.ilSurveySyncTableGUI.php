<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Survey sync table GUI class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id$
 *
 * @ingroup ModulesSurveyQuestionPool
 */
class ilSurveySyncTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 *
	 * @param object $a_parent_obj parent gui object
	 * @param string $a_parent_cmd parent default command
	 * @param ilSurveyQuestion $a_question
	 */
	function __construct($a_parent_obj, $a_parent_cmd, SurveyQuestion $a_question)
	{
		global $ilCtrl, $lng;

		$this->question = $a_question;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setId("il_svy_spl_sync");

		$this->setTitle($this->question->getTitle());
		$this->setDescription($lng->txt("survey_sync_question_copies_info"));
		
		$this->addCommandButton("synccopies", $lng->txt("survey_sync_question_copies"));
		$this->addCommandButton("cancel", $lng->txt("cancel"));

		// $this->setSelectAllCheckbox("id[]");
		$this->addColumn("", "", 1);
		$this->addColumn($lng->txt("title"), "");
		$this->addColumn($lng->txt("path"), "");
		$this->addColumn($lng->txt("message"), "");
	
		$this->setDefaultOrderField("title");
		$this->setDefaultOrderDirection("asc");

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.il_svy_qpl_sync.html", "Modules/SurveyQuestionPool");

		$this->importData();
	}

	/**
	 * Import data from DB
	 */
	protected function importData()
	{
		global $ilAccess, $lng;
		
		$table_data = array();
		foreach($this->question->getCopyIds(true) as $survey_id => $questions)
		{
			// check permissions for "parent" survey
			$can_write = false;
			$ref_ids = ilObject::_getAllReferences($survey_id);
			foreach($ref_ids as $ref_id)
			{
				if($ilAccess->checkAccess("edit", "", $ref_id))
				{
					$can_write = true;
					break;
				}
			}
			
			$message = "";
			if(!$can_write)
			{
				$message = $lng->txt("survey_sync_insufficient_permissions");
			}
			
			$table_data[] = array(
				"id" => null,
				"title" => ilObject::_lookupTitle($survey_id),
				"path" => $this->buildPath($ref_ids),
				"message" => $message
				);						
				
			if($can_write)
			{
				foreach($questions as $question_id)
				{
					$table_data[] = array(
						"id" => $question_id,
						"title" => SurveyQuestion::_getTitle($question_id),
						"path" => null,
						"message" => null
						);
				}				
			}
		}		

		$this->setData($table_data);
	}
	
	/**
	 * Fill table row
	 *
	 * @param array $a_set data array
	 */
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl;

		// survey
		if(!$a_set["id"])
		{
			$this->tpl->setVariable("TITLE", $a_set["title"]);
			$this->tpl->setVariable("PATH", implode("<br />", $a_set["path"]));
			$this->tpl->setVariable("MESSAGE", $a_set["message"]);	
		}
		// question
		else
		{
			$this->tpl->setVariable("TITLE", "- ".$a_set["title"]);
			
			$this->tpl->setCurrentBlock("checkbox");
			$this->tpl->setVariable("ID", $a_set["id"]);
			$this->tpl->parseCurrentBlock();
		}
	}
	
    /**
 	 * Build path with deep-link
	 *
	 * @param	array	$ref_ids
	 * @return	array 
	 */
	protected function buildPath($ref_ids)
	{
		global $tree, $ilCtrl;

		include_once 'classes/class.ilLink.php';
		
		if(!count($ref_ids))
		{
			return false;
		}
		foreach($ref_ids as $ref_id)
		{
			$path = "...";
			$counter = 0;
			$path_full = $tree->getPathFull($ref_id);
			foreach($path_full as $data)
			{
				if(++$counter < (count($path_full)-1))
				{
					continue;
				}
				$path .= " &raquo; ";
				if($ref_id != $data['ref_id'])
				{
					$path .= $data['title'];
				}
				else
				{
					$path .= ('<a target="_top" href="'.
							  ilLink::_getLink($data['ref_id'],$data['type']).'">'.
							  $data['title'].'</a>');
				}
			}

			$result[] = $path;
		}
		return $result;
	}
}

?>