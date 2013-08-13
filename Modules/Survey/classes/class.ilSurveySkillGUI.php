<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Survey skill service GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ilCtrl_Calls ilSurveySkillGUI: ilSurveySkillThresholdsGUI
 * @ingroup ModulesSurvey
 */
class ilSurveySkillGUI
{
	/**
	 * Constructor
	 *
	 * @param object $a_survey
	 */
	function __construct(ilObjSurvey $a_survey)
	{
		$this->survey = $a_survey;
	}
	
	/**
	 * Execute command
	 */
	function executeCommand()
	{
		global $ilCtrl;
		
		$cmd = $ilCtrl->getCmd();
		$next_class = $ilCtrl->getNextClass();
		
		switch ($next_class)
		{
			case 'ilsurveyskillthresholdsgui':
				$this->setSubTabs("skill_thresholds");
				include_once("./Modules/Survey/classes/class.ilSurveySkillThresholdsGUI.php");
				$gui = new ilSurveySkillThresholdsGUI($this->survey);
				$ilCtrl->forwardCommand($gui);
				break;
				
			default:
				if (in_array($cmd, array("listQuestionAssignment",
					"assignSkillToQuestion", "selectSkillForQuestion",
					"removeSkillFromQuestion")))
				{
					$this->setSubTabs("survey_skill_assign");
					$this->$cmd();
				}
				break;
		}
	}
	
	/**
	 * List question to skill assignment
	 */
	function listQuestionAssignment()
	{
		global $tpl;

		include_once("./Modules/Survey/classes/class.ilSurveySkillAssignmentTableGUI.php");
		$tab = new ilSurveySkillAssignmentTableGUI($this, "listQuestionAssignment",
			$this->survey);
		$tpl->setContent($tab->getHTML());
	}
	
	/**
	 * Assign skill to question
	 */
	function assignSkillToQuestion()
	{
		global $ilUser, $tpl, $ilCtrl, $lng, $ilTabs;

		$ilCtrl->saveParameter($this, "q_id");
		

		include_once("./Services/Skill/classes/class.ilSkillSelectorGUI.php");		
		$sel = new ilSkillSelectorGUI($this, "assignSkillToQuestion", $this, "selectSkillForQuestion");
		if (!$sel->handleCommand())
		{
			$tpl->setContent($sel->getHTML());
		}

return;
		/*
		include_once("./Services/Skill/classes/class.ilSkillTree.php");
		$skill_tree = new ilSkillTree();
		
		require_once ("./Modules/Survey/classes/class.ilSurveySkillExplorer.php");
		$exp = new ilSurveySkillExplorer($ilCtrl->getLinkTarget($this, "assignSkillToQuestion"));
		$exp->setTargetGet("obj_id");
		
		$exp->setExpandTarget($ilCtrl->getLinkTarget($this, "assignSkillToQuestion"));
		
		if ($_GET["skpexpand"] == "")
		{
			$expanded = $skill_tree->readRootId();
		}
		else
		{
			$expanded = $_GET["skpexpand"];
		}

		$exp->setExpand($expanded);
		// build html-output
		$exp->setOutput(0);
		$output = $exp->getOutput();

		// asynchronous output
		if ($ilCtrl->isAsynch())
		{
			echo $output; exit;
		}

		$tpl->setContent($output); */
	}
	
	/**
	 * Select skill for question
	 */
	function selectSkillForQuestion()
	{
		global $ilCtrl, $lng;
		
		include_once("./Modules/Survey/classes/class.ilSurveySkill.php");
		$skill_survey = new ilSurveySkill($this->survey);
		$skill_id_parts = explode(":", $_GET["selected_skill"]);
		$skill_survey->addQuestionSkillAssignment((int) $_GET["q_id"],
			(int) $skill_id_parts[0], (int) $skill_id_parts[1]);
		ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
		
		$ilCtrl->redirect($this, "listQuestionAssignment");
	}
	
	/**
	 * Remove skill from question
	 */
	function removeSkillFromQuestion()
	{
		global $ilCtrl, $lng;
		
		include_once("./Modules/Survey/classes/class.ilSurveySkill.php");
		$skill_survey = new ilSurveySkill($this->survey);
		$skill_survey->removeQuestionSkillAssignment((int) $_GET["q_id"]);
		ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
		
		$ilCtrl->redirect($this, "listQuestionAssignment");
	}
	
	/**
	 * Set subtabs
	 *
	 * @param string $a_activate activate sub tab (ID)
	 */
	function setSubTabs($a_activate)
	{
		global $ilTabs, $lng, $ilCtrl;

		$ilTabs->addSubtab("survey_skill_assign",
			$lng->txt("survey_skill_assign"),
			$ilCtrl->getLinkTargetByClass("ilsurveyskillgui", "listQuestionAssignment"));

		$ilTabs->addSubTab("skill_thresholds",
			$lng->txt("survey_skill_thresholds"),
			$ilCtrl->getLinkTargetByClass("ilsurveyskillthresholdsgui", "listCompetences"));

		$ilTabs->activateSubtab($a_activate);
	}

}

?>
