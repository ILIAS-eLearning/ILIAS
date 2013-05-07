<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Skill/Competence handling in surveys
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup 
 */
class ilSurveySkill
{
	protected $q_skill = array();	// key: question id, value:
									// array("base_skill_id" =>..., "tref_id" =>... )
	
	/**
	 * Constructor
	 *
	 * @param
	 * @return
	 */
	function __construct(ilObjSurvey $a_survey)
	{
		$this->survey = $a_survey;
		$this->read();
	}
	
	/**
	 * Read
	 *
	 * @param
	 * @return
	 */
	function read()
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT * FROM svy_quest_skill ".
			" WHERE survey_id = ".$ilDB->quote($this->survey->getId(), "integer")
			);
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$this->q_skill[$rec["q_id"]] = array("q_id" => $rec["q_id"],
				"base_skill_id" => $rec["base_skill_id"],
				"tref_id" => $rec["tref_id"]);
		}
	}
	
	/**
	 * Get skill for question
	 *
	 * @param int $a_question_id question id
	 * @return array skill array
	 */
	function getSkillForQuestion($a_question_id)
	{
		if (isset($this->q_skill[$a_question_id]))
		{
			return $this->q_skill[$a_question_id];
		}
		return false;
	}
	
	/**
	 * Get questions for skill
	 *
	 * @param
	 * @return
	 */
	function getQuestionsForSkill($a_base_skill_id, $a_tref_id)
	{
		$q_ids = array();
		foreach ($this->q_skill as $q_id => $s)
		{
			if ($s["base_skill_id"] == $a_base_skill_id &&
				$s["tref_id"] == $a_tref_id)
			{
				$q_ids[] = $q_id;
			}
		}
		return $q_ids;
	}
	
	
	/**
	 * Add survey question to skill assignment
	 *
	 * @param int $a_question_id question id
	 * @param int $a_base_skill_id base skill id
	 * @param int $a_tref_id skill template reference id (0, if no template involved)
	 */
	function addQuestionSkillAssignment($a_question_id, $a_base_skill_id, $a_tref_id)
	{
		global $ilDB;
		
		$ilDB->replace("svy_quest_skill",
			array("q_id" => array("integer", $a_question_id)),
			array(
				"survey_id" => array("integer", $this->survey->getId()),
				"base_skill_id" => array("integer", $a_base_skill_id),
				"tref_id" => array("integer", $a_tref_id)
				)
			);
		$this->q_skill[$a_question_id] = array("q_id" => $a_question_id,
			"base_skill_id" => $a_base_skill_id,
			"tref_id" => $a_tref_id);

	}
	
	/**
	 * Remove question skill assignment
	 *
	 * @param int $a_question_id question id
	 */
	function removeQuestionSkillAssignment($a_question_id)
	{
		global $ilDB;
		
		$ilDB->manipulate("DELETE FROM svy_quest_skill WHERE ".
			" q_id = ".$ilDB->quote($a_question_id, "integer")
			);
		unset($this->q_skill[$a_question_id]);
	}

	/**
	 * Get skill for question
	 *
	 * @param int $a_question_id question id
	 * @return array skill array
	 */
	function getAllAssignedSkillsAsOptions()
	{
		$skills = array();
		include_once("./Services/Skill/classes/class.ilBasicSkill.php");
		foreach ($this->q_skill as $sk)
		{
			$skills[$sk["base_skill_id"].":".$sk["tref_id"]] = 
				ilBasicSkill::_lookupTitle($sk["base_skill_id"]);
		}
		return $skills;
	}

	/**
	 * Determine skill levels for appraisee
	 *
	 * @param $a_appraisee_id int user id of appraisee
	 * @return array array with lots of information
	 */
	function determineSkillLevelsForAppraisee($a_appraisee_id, $a_self_eval = false)
	{
		$skills = array();

		// get all skills
		$opts = $this->getAllAssignedSkillsAsOptions();
		foreach ($opts as $k => $title)
		{
			$k = explode(":", $k);
			
			$bs = new ilBasicSkill((int) $k[0]);
			$ld = $bs->getLevelData();
			
			$skills[] = array(
				"base_skill_id" => (int) $k[0],
				"tref_id" => (int) $k[1],
				"skill_title" => $title,
				"level_data" => $ld
				);
		}

		if (!$a_self_eval)
		{
			$finished_ids = $this->survey->getFinishedIdsForAppraiseeId($a_appraisee_id, true);
		}
		else
		{
			$finished_id = $this->survey->getFinishedIdForAppraiseeIdAndRaterId($a_appraisee_id, $a_appraisee_id);
			if ($finished_id > 0)
			{
				$finished_ids = array($finished_id);
			}
		}
		
		if(!sizeof($finished_ids))
		{
			$finished_ids = array(-1);
		}

		$results = $this->survey->getUserSpecificResults($finished_ids);
		foreach ($skills as $k => $s)
		{
			$q_ids = $this->getQuestionsForSkill($s["base_skill_id"], $s["tref_id"]);
			$mean_sum = 0;
			foreach ($q_ids as $q_id)
			{
				$qmean = 0;
				if (is_array($results[$q_id]))
				{
					$cnt = 0;
					$sum = 0;
					foreach ($results[$q_id] as $r)
					{
						// this is a workaround, since we only get arrays from
						// getUserSpecificResults() 
						$r = explode(" - ", $r);
						$sum+= (int) $r[0];
						$cnt++;
					}
					if ($cnt > 0)
					{
						$qmean = $sum/$cnt;
					}
				}
				$mean_sum+= $qmean;
			}
			$skills[$k]["mean_sum"] = $mean_sum;
			
			include_once("./Modules/Survey/classes/class.ilSurveySkillThresholds.php");
			$skthr = new ilSurveySkillThresholds($this->survey);
			$thresholds = $skthr->getThresholds();
			foreach ($skills[$k]["level_data"] as $l)
			{
				$t = $thresholds[$l["id"]][$s["tref_id"]];
				if ($t > 0 && $mean_sum >= $t)
				{
					$skills[$k]["new_level"] = $l["title"];
					$skills[$k]["new_level_id"] = $l["id"];
				}
			}
		}
		
		return $skills;
	}
	
	
	/**
	 * Determine max scales and questions
	 *
	 * @param
	 * @return
	 */
	function determineMaxScale($a_base_skill, $a_tref_id = 0)
	{
		include_once("./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php");
		include_once("./Modules/Survey/classes/class.ilSurveySkill.php");
		$ssk = new ilSurveySkill($this->survey);
		$question_ids = $ssk->getQuestionsForSkill($a_base_skill, $a_tref_id);
		$scale_sum = 0;
		foreach ($question_ids as $q_id)
		{
			$q = SurveyQuestion::_instanciateQuestion($q_id);
			$cats = $q->getCategories();
			$max_scale = 0;
			for($i = 0; $i<= $cats->getCategoryCount(); $i++)
			{
				$c = $cats->getCategory($i);
				$n = $c->neutral;
				$s = $c->scale;
				if (!$c->neutral)
				{
					if ($c->scale > $max_scale)
					{
						$max_scale = $c->scale;
					}
				}
			}
			$scale_sum+= $max_scale;
		}
		
		return $scale_sum;
	}

	/**
	 * Write appraisee skills
	 *
	 * @param
	 * @return
	 */
	function writeAppraiseeSkills($a_app_id)
	{
		$new_levels = $this->determineSkillLevelsForAppraisee($a_app_id);
		foreach ($new_levels as $nl)
		{
			if ($nl["new_level_id"] > 0)
			{
				ilBasicSkill::writeUserSkillLevelStatus($nl["new_level_id"],
					$a_app_id, $this->survey->getRefId(), $nl["tref_id"], ilBasicSkill::ACHIEVED, true);
			}
		}
	}
	
}

?>
