<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for survey questions to skill assignment
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilSurveySkillAssignmentTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_survey)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		$this->object = $a_survey;
		include_once("./Modules/Survey/classes/class.ilSurveySkill.php");
		$this->skill_survey = new ilSurveySkill($a_survey);
		
		include_once("./Services/Skill/classes/class.ilSkillTree.php");
		$this->skill_tree = new ilSkillTree();
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->getQuestions();
		//$this->setTitle($lng->txt("survey_questions_to_skill_ass"));
		
		$this->addColumn($this->lng->txt("question"));
		$this->addColumn($this->lng->txt("survey_skill"));
		$this->addColumn($this->lng->txt("actions"));
		
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.svy_skill_ass_row.html", "Modules/Survey");

//		$this->addMultiCommand("", $lng->txt(""));
//		$this->addCommandButton("", $lng->txt(""));
	}
	
	/**
	 * Get questions
	 *
	 * @param
	 * @return
	 */
	function getQuestions()
	{
		global $ilCtrl, $lng;
		
		$survey_questions = $this->object->getSurveyQuestions();

		if (count($survey_questions) > 0)
		{
			$table_data = array();
			$last_questionblock_id = $position = $block_position = 0;
			foreach ($survey_questions as $question_id => $data)
			{
				// it is only possible to assign  to a subset
				// of question types: single choice(2)
				$supported = false;
				if (in_array($data["questiontype_fi"], array(2)))
				{
					$supported = true;
				}

				$id = $data["question_id"];
				
				$table_data[$id] = array("id" => $id,
					"type" => "question",
					"supported" => $supported,
					"heading" => $data["heading"],
					"title" => $data["title"],
					"description" => $data["description"],
					"author" => $data["author"],
					"obligatory" => (bool)$data["obligatory"]);

			}
		}
		$this->setData($table_data);
	}
	
	
	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl;

		$ilCtrl->setParameter($this->parent_obj, "q_id", $a_set["id"]);

		if ($a_set["supported"])
		{
			$this->tpl->setCurrentBlock("cmd");
			$this->tpl->setVariable("HREF_CMD",
				$ilCtrl->getLinkTarget($this->parent_obj,
				"assignSkillToQuestion"));
			$this->tpl->setVariable("TXT_CMD", $lng->txt("survey_assign_competence"));
			$this->tpl->parseCurrentBlock();
			
			if ($s = $this->skill_survey->getSkillForQuestion($a_set["id"]))
			{
				$this->tpl->setCurrentBlock("cmd");
				$this->tpl->setVariable("HREF_CMD",
					$ilCtrl->getLinkTarget($this->parent_obj,
					"removeSkillFromQuestion"));
				$this->tpl->setVariable("TXT_CMD", $lng->txt("survey_remove_competence"));
				$this->tpl->parseCurrentBlock();
				
				include_once("./Services/Skill/classes/class.ilBasicSkill.php");
				$this->tpl->setVariable("COMPETENCE",
					ilBasicSkill::_lookupTitle($s["base_skill_id"], $s["tref_id"]));

				//var_dump($a_set);
				$path = $this->skill_tree->getSkillTreePath($s["base_skill_id"], $s["tref_id"]);
				$path_nodes = array();
				foreach ($path as $p)
				{
					if ($p["child"] > 1 && $p["skill_id"] != $s["base_skill_id"])
					{
						$path_nodes[] = ilBasicSkill::_lookupTitle($p["skill_id"], $p["tref_id"]);
					}
				}
				$this->tpl->setVariable("PATH", implode($path_nodes, " > "));
				$this->tpl->setVariable("COMP_ID", "comp_".$a_set["id"]);
				
				/*include_once("./Services/UIComponent/Tooltip/classes/class.ilTooltipGUI.php");
				ilTooltipGUI::addTooltip("comp_".$a_set["id"],
					ilBasicSkill::_lookupDescription($s["base_skill_id"]));*/
			}
		}
		else
		{
			$this->tpl->setVariable("NOT_SUPPORTED", $lng->txt("svy_skl_comp_assignm_not_supported"));
		}
		
		$this->tpl->setVariable("QUESTION_TITLE", $a_set["title"]);
		
		$ilCtrl->setParameter($this->parent_obj, "q_id", "");

	}

}
?>
