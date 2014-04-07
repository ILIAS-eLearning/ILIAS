<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for skill list in survey
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ModulesSurvey
 */
class ilSurveySkillTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_survey)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		$this->survey = $a_survey;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->getSkills();
		$this->setTitle($lng->txt("survey_competences"));

		include_once("./Services/Skill/classes/class.ilSkillTree.php");
		$this->skill_tree = new ilSkillTree();

		include_once("./Modules/Survey/classes/class.ilSurveySkillThresholds.php");
		$this->skill_thres = new ilSurveySkillThresholds($a_survey);
		$this->thresholds = $this->skill_thres->getThresholds();

		$this->addColumn($this->lng->txt("survey_skill"));
		$this->addColumn($this->lng->txt("survey_skill_nr_q"));
		$this->addColumn($this->lng->txt("survey_skill_max_scale_points"));
		$this->addColumn($this->lng->txt("survey_up_to_x_points"));
		$this->addColumn($this->lng->txt("actions"));

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.svy_skill_row.html", "Modules/Survey");

		//$this->addMultiCommand("", $lng->txt(""));
		//$this->addCommandButton("", $lng->txt(""));
	}
	
	/**
	 * Get skills
	 *
	 * @param
	 * @return
	 */
	function getSkills()
	{
		include_once("./Modules/Survey/classes/class.ilSurveySkill.php");
		$sskill = new ilSurveySkill($this->survey);
		$opts = $sskill->getAllAssignedSkillsAsOptions();
		$data = array();
		foreach ($opts as $k => $o)
		{
			$v = explode(":", $k);

			$question_ids = $sskill->getQuestionsForSkill($v[0], $v[1]);
			$scale_sum = $sskill->determineMaxScale($v[0], $v[1]);

			$data[] = array("title" => ilBasicSkill::_lookupTitle($v[0], $v[1]),
				"base_skill" => $v[0],
				"tref_id" => $v[1],
				"nr_of_q" => count($question_ids),
				"scale_sum" => $scale_sum
				);
		}
		
		$this->setData($data);
	}
	
	
	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl;

		$ilCtrl->setParameter($this->parent_obj, "sk_id", $a_set["base_skill"]);
		$ilCtrl->setParameter($this->parent_obj, "tref_id", $a_set["tref_id"]);

		$this->tpl->setVariable("COMPETENCE",
			ilBasicSkill::_lookupTitle($a_set["base_skill"], $a_set["tref_id"]));
		$path = $this->skill_tree->getSkillTreePath($a_set["base_skill"], $a_set["tref_id"]);
		$path_nodes = array();
		foreach ($path as $p)
		{
			if ($p["child"] > 1 && $p["skill_id"] != $a_set["base_skill"])
			{
				$path_nodes[] = ilBasicSkill::_lookupTitle($p["skill_id"], $p["tref_id"]);
			}
		}
		$this->tpl->setVariable("PATH", implode($path_nodes, " > "));



		$this->tpl->setVariable("NR_OF_QUESTIONS", $a_set["nr_of_q"]);
		$this->tpl->setVariable("MAX_SCALE_POINTS", $a_set["scale_sum"]);
		$this->tpl->setVariable("CMD", $ilCtrl->getLinkTarget($this->parent_obj, "listSkillThresholds"));
		$this->tpl->setVariable("ACTION", $lng->txt("edit"));
		
		include_once("./Services/Skill/classes/class.ilBasicSkill.php");
		$bs = new ilBasicSkill($a_set["base_skill"]);
		$ld = $bs->getLevelData();
		foreach ($ld as $l)
		{
			$this->tpl->setCurrentBlock("points");
			$this->tpl->setVariable("LEV", $l["title"]);

			$tr = $this->thresholds[$l["id"]][$a_set["tref_id"]];
			if ((int) $tr != 0)
			{
				$this->tpl->setVariable("THRESHOLD", (int) $tr);
			}
			else
			{
				$this->tpl->setVariable("THRESHOLD", "");
			}
			$this->tpl->parseCurrentBlock();
		}
	}

}
?>
