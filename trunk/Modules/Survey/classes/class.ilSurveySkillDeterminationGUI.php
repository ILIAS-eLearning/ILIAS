<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Survey skill determination GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ilCtrl_Calls ilSurveySkillDeterminationGUI:
 * @ingroup ModulesSurvey
 */
class ilSurveySkillDeterminationGUI
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
		
		$cmd = $ilCtrl->getCmd("listSkillChanges");
		
		//$ilCtrl->saveParameter($this, array("sk_id", "tref_id"));
		
		if (in_array($cmd, array("listSkillChanges", "writeSkills")))
		{
			$this->$cmd();
		}
	}
	
	/**
	 * List skill changes
	 */
	function listSkillChanges()
	{
		global $tpl, $ilToolbar, $lng, $ilCtrl;

		include_once("./Modules/Survey/classes/class.ilSurveySkillChangesTableGUI.php");

//		$ilToolbar->addButton($lng->txt("survey_write_skills"),
//			$ilCtrl->getLinkTarget($this, "writeSkills"));
		
		$apps = $this->survey->getAppraiseesData();
		$ctpl = new ilTemplate("tpl.svy_skill_list_changes.html", true, true, "Modules/Survey");
		foreach ($apps as $app)
		{
			$changes_table = new ilSurveySkillChangesTableGUI($this, "listSkillChanges",
				$this->survey, $app);
			
			$ctpl->setCurrentBlock("appraisee");
			$ctpl->setVariable("LASTNAME", $app["lastname"]);
			$ctpl->setVariable("FIRSTNAME", $app["firstname"]);
			
			$ctpl->setVariable("CHANGES_TABLE", $changes_table->getHTML());
			
			$ctpl->parseCurrentBlock();
		}
		
		$tpl->setContent($ctpl->get());
	}
	
	/**
	 * Write skills
	 *
	 * @param
	 * @return
	 */
	function writeSkills()
	{
		global $lng, $ilCtrl;
return;
		include_once("./Modules/Survey/classes/class.ilSurveySkill.php");
		$sskill = new ilSurveySkill($this->survey);
		$apps = $this->survey->getAppraiseesData();
		$ctpl = new ilTemplate("tpl.svy_skill_list_changes.html", true, true, "Modules/Survey");
		foreach ($apps as $app)
		{
			$new_levels = $sskill->determineSkillLevelsForAppraisee($app["user_id"]);
			foreach ($new_levels as $nl)
			{
				if ($nl["new_level_id"] > 0)
				{
					ilBasicSkill::writeUserSkillLevelStatus($nl["new_level_id"],
						$app["user_id"], $this->survey->getRefId(), $nl["tref_id"], ilBasicSkill::ACHIEVED);
				}
			}
		}
		ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
		$ilCtrl->redirect($this, "listSkillChanges");
	}
	
}

?>
