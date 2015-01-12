<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Survey skill thresholds GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ilCtrl_Calls ilSurveySkillThresholdsGUI:
 * @ingroup ModulesSurvey
 */
class ilSurveySkillThresholdsGUI
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
		
		$ilCtrl->saveParameter($this, array("sk_id", "tref_id"));
		
		if (in_array($cmd, array("listCompetences", "listSkillThresholds", "selectSkill",
			"saveThresholds")))
		{
			$this->$cmd();
		}
	}
	
	/**
	 * List competences
	 *
	 * @param
	 * @return
	 */
	function listCompetences()
	{
		global $tpl;
		
		include_once("./Modules/Survey/classes/class.ilSurveySkillTableGUI.php");
		$tab = new ilSurveySkillTableGUI($this, "listCompetences", $this->survey);
		$tpl->setContent($tab->getHTML());
	}
	
	
	/**
	 * List skill thresholds
	 */
	function listSkillThresholds()
	{
		global $tpl, $ilToolbar, $lng, $ilCtrl, $ilTabs;
		
		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt("svy_back"),
			$ilCtrl->getLinkTarget($this, "listCompetences"));
		
		include_once("./Modules/Survey/classes/class.ilSurveySkillThresholdsTableGUI.php");
		$tab = new ilSurveySkillThresholdsTableGUI($this, "listSkillThresholds",
			$this->survey, (int) $_GET["sk_id"], (int) $_GET["tref_id"]);
		$tpl->setContent($tab->getHTML());
	}
	
	/**
	 * Select skill
	 *
	 * @param
	 * @return
	 */
	function selectSkill()
	{
		global $ilCtrl;
		
		$o = explode(":", $_POST["skill"]);
		$ilCtrl->setParameter($this, "sk_id", (int) $o[0]);
		$ilCtrl->setParameter($this, "tref_id", (int) $o[1]);
		$ilCtrl->redirect($this, "listSkillThresholds");
	}
	
	/**
	 * Save Thresholds
	 *
	 * @param
	 * @return
	 */
	function saveThresholds()
	{
		global $ilCtrl, $lng;
		
		include_once("./Modules/Survey/classes/class.ilSurveySkillThresholds.php");
		$thres = new ilSurveySkillThresholds($this->survey);

		if (is_array($_POST["threshold"]))
		{
			foreach ($_POST["threshold"] as $l => $t)
			{
				$thres->writeThreshold((int) $_GET["sk_id"],
					(int) $_GET["tref_id"], (int) $l, (int) $t);
			}
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), 1);
		}
		
		$ilCtrl->redirect($this, "listSkillThresholds");
	}
	
}

?>
