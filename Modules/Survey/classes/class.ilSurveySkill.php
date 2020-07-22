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
    /**
     * @var ilDB
     */
    protected $db;

    protected $q_skill = array();	// key: question id, value:
    // array("base_skill_id" =>..., "tref_id" =>... )
    /**
     * @var ilLogger
     */
    protected $log;

    /**
     * Constructor
     *
     * @param
     * @return
     */
    public function __construct(ilObjSurvey $a_survey)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->survey = $a_survey;
        $this->read();
        $this->log = ilLoggerFactory::getLogger("svy");
    }
    
    /**
     * Read
     *
     * @param
     * @return
     */
    public function read()
    {
        $ilDB = $this->db;
        
        $set = $ilDB->query(
            "SELECT * FROM svy_quest_skill " .
            " WHERE survey_id = " . $ilDB->quote($this->survey->getId(), "integer")
        );
        
        include_once("./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php");
        
        while ($rec = $ilDB->fetchAssoc($set)) {
            if (SurveyQuestion::_questionExists($rec["q_id"])) {
                $this->q_skill[$rec["q_id"]] = array("q_id" => $rec["q_id"],
                    "base_skill_id" => $rec["base_skill_id"],
                    "tref_id" => $rec["tref_id"]);
            }
        }
    }
    
    /**
     * Get skill for question
     *
     * @param int $a_question_id question id
     * @return array skill array
     */
    public function getSkillForQuestion($a_question_id)
    {
        if (isset($this->q_skill[$a_question_id])) {
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
    public function getQuestionsForSkill($a_base_skill_id, $a_tref_id)
    {
        $q_ids = array();
        foreach ($this->q_skill as $q_id => $s) {
            if ($s["base_skill_id"] == $a_base_skill_id &&
                $s["tref_id"] == $a_tref_id) {
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
    public function addQuestionSkillAssignment($a_question_id, $a_base_skill_id, $a_tref_id)
    {
        $ilDB = $this->db;
        
        $ilDB->replace(
            "svy_quest_skill",
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
        
        // add usage
        include_once("./Services/Skill/classes/class.ilSkillUsage.php");
        ilSkillUsage::setUsage($this->survey->getId(), $a_base_skill_id, $a_tref_id);
    }
    
    /**
     * Remove question skill assignment
     *
     * @param int $a_question_id question id
     */
    public function removeQuestionSkillAssignment($a_question_id)
    {
        $ilDB = $this->db;
        
        // read skills that are assigned to the quesiton
        $set = $ilDB->query(
            "SELECT * FROM svy_quest_skill " .
            " WHERE q_id = " . $ilDB->quote($a_question_id, "integer")
        );
        $skills = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $skills[] = array("skill_id" => $rec["base_skill_id"],
                "tref_id" => $rec["tref_id"]);
        }
        
        // remove assignment of question
        $ilDB->manipulate(
            "DELETE FROM svy_quest_skill WHERE " .
            " q_id = " . $ilDB->quote($a_question_id, "integer")
        );
        unset($this->q_skill[$a_question_id]);
        
        $this->removeUsagesOfSkills($skills);
    }

    /**
     * Remove question skill assignment
     *
     * @param int $a_question_id question id
     */
    public static function handleQuestionDeletion($a_question_id, $a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        if (ilObject::_lookupType($a_obj_id) == "svy") {
            // mantis 11691
            include_once './Modules/Survey/classes/class.ilObjSurvey.php';
            $svy = new ilObjSurvey($a_obj_id, false);
            $svy_skill = new ilSurveySkill($svy);
            $svy_skill->removeQuestionSkillAssignment($a_question_id);
        }
    }
    
    /**
     * Remove usages of skills
     *
     * This function checks, if the skills are really not in use anymore
     * @param array array of arrays with keys "skill_id" and "tref_id"
     */
    public function removeUsagesOfSkills($a_skills)
    {
        $used_skills = array();
        foreach ($a_skills as $skill) {
            if ($this->isSkillAssignedToQuestion($skill["skill_id"], $skill["tref_id"])) {
                $used_skills[] = $skill["skill_id"] . ":" . $skill["tref_id"];
            }
        }
        reset($a_skills);
        
        // now remove all usages that have been confirmed
        include_once("./Services/Skill/classes/class.ilSkillUsage.php");
        //var_dump($a_skills);
        //var_dump($used_skills); exit;
        foreach ($a_skills as $skill) {
            if (!in_array($skill["skill_id"] . ":" . $skill["tref_id"], $used_skills)) {
                ilSkillUsage::setUsage($this->survey->getId(), $skill["skill_id"], $skill["tref_id"], false);
            }
        }
    }
    
    /**
     * Is skill assigned to any question?
     *
     * @param
     * @return
     */
    public function isSkillAssignedToQuestion($a_skill_id, $a_tref_id)
    {
        $ilDB = $this->db;
        
        $set = $ilDB->query(
            "SELECT * FROM svy_quest_skill " .
            " WHERE base_skill_id = " . $ilDB->quote($a_skill_id, "integer") .
            " AND tref_id = " . $ilDB->quote($a_tref_id, "integer") .
            " AND survey_id = " . $ilDB->quote($this->survey->getId(), "integer")
        );
        if ($rec = $ilDB->fetchAssoc($set)) {
            return true;
        }
        return false;
    }
    

    /**
     * Get skill for question
     *
     * @param int $a_question_id question id
     * @return array skill array
     */
    public function getAllAssignedSkillsAsOptions()
    {
        $skills = array();
        include_once("./Services/Skill/classes/class.ilBasicSkill.php");
        foreach ($this->q_skill as $sk) {
            $skills[$sk["base_skill_id"] . ":" . $sk["tref_id"]] =
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
    public function determineSkillLevelsForAppraisee($a_appraisee_id, $a_self_eval = false)
    {
        $skills = array();

        // get all skills
        $opts = $this->getAllAssignedSkillsAsOptions();
        foreach ($opts as $k => $title) {
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

        if (!$a_self_eval) {
            $finished_ids = $this->survey->getFinishedIdsForAppraiseeId($a_appraisee_id, true);
        } else {
            $finished_id = $this->survey->getFinishedIdForAppraiseeIdAndRaterId($a_appraisee_id, $a_appraisee_id);
            if ($finished_id > 0) {
                $finished_ids = array($finished_id);
            }
        }
        
        if (!sizeof($finished_ids)) {
            $finished_ids = array(-1);
        }

        $results = $this->survey->getUserSpecificResults($finished_ids);
        $this->log->debug("Finished IDS: " . print_r($finished_ids, true));
        foreach ($skills as $k => $s) {
            $q_ids = $this->getQuestionsForSkill($s["base_skill_id"], $s["tref_id"]);
            $this->log->debug("Skill: " . $s["base_skill_id"] . ":" . $s["tref_id"] . ", Questions: " . implode(",", $q_ids));
            $mean_sum = 0;
            foreach ($q_ids as $q_id) {
                $qmean = 0;
                if (is_array($results[$q_id])) {
                    $cnt = 0;
                    $sum = 0;
                    foreach ($results[$q_id] as $uid => $answer) {	// answer of user $uid for question $q_id
                        // $answer has the scale values as keys and the answer texts as values.
                        // In case of single choice this is an array with one key => value pair.
                        // For multiple choice questions (currently not supported for being used for competences)
                        // multiple elements may be in the array (in the future).
                        $scale_values = array_keys($answer); // scale values of the answer
                        $this->log->debug("User answer (scale values): " . print_r($scale_values, true));
                        $sum += array_sum($scale_values);
                        $cnt += sizeof($scale_values); // nr of answers (always one in the case of single choice)
                    }
                    if ($cnt > 0) {
                        $qmean = $sum / $cnt;
                    }
                    $this->log->debug("MEAN: " . $qmean);
                }
                $mean_sum += $qmean;
                $this->log->debug("MEAN SUM: " . $mean_sum);
            }
            $skills[$k]["mean_sum"] = $mean_sum;
            
            include_once("./Modules/Survey/classes/class.ilSurveySkillThresholds.php");
            $skthr = new ilSurveySkillThresholds($this->survey);
            $thresholds = $skthr->getThresholds();
            foreach ($skills[$k]["level_data"] as $l) {
                $t = $thresholds[$l["id"]][$s["tref_id"]];
                if ($t > 0 && $mean_sum >= $t) {
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
    public function determineMaxScale($a_base_skill, $a_tref_id = 0)
    {
        include_once("./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php");
        include_once("./Modules/Survey/classes/class.ilSurveySkill.php");
        $ssk = new ilSurveySkill($this->survey);
        $question_ids = $ssk->getQuestionsForSkill($a_base_skill, $a_tref_id);
        $scale_sum = 0;
        foreach ($question_ids as $q_id) {
            $q = SurveyQuestion::_instanciateQuestion($q_id);
            if (!is_object($q)) {
                continue;
            }
            $cats = $q->getCategories();
            $max_scale = 0;
            for ($i = 0; $i <= $cats->getCategoryCount(); $i++) {
                $c = $cats->getCategory($i);
                $n = $c->neutral;
                $s = $c->scale;
                if (!$c->neutral) {
                    if ($c->scale > $max_scale) {
                        $max_scale = $c->scale;
                    }
                }
            }
            $scale_sum += $max_scale;
        }
        
        return $scale_sum;
    }

    /**
     * Write appraisee skills
     *
     * @param int $user_id
     */
    public function writeAppraiseeSkills($a_app_id)
    {
        // write raters evaluation
        $new_levels = $this->determineSkillLevelsForAppraisee($a_app_id);
        foreach ($new_levels as $nl) {
            if ($nl["new_level_id"] > 0) {
                ilBasicSkill::writeUserSkillLevelStatus(
                    $nl["new_level_id"],
                    $a_app_id,
                    $this->survey->getRefId(),
                    $nl["tref_id"],
                    ilBasicSkill::ACHIEVED,
                    true
                );
            }
        }

        // write self evaluation
        $this->writeSelfEvalSkills($a_app_id);
    }

    /**
     * Write skills on self evaluation
     *
     * @param int $user_id
     */
    public function writeSelfEvalSkills(int $user_id)
    {
        if ($user_id > 0 && in_array($this->survey->getMode(), [ilObjSurvey::MODE_SELF_EVAL, ilObjSurvey::MODE_360])) {
            $new_levels = $this->determineSkillLevelsForAppraisee($user_id, true);
            foreach ($new_levels as $nl) {
                if ($nl["new_level_id"] > 0) {
                    ilBasicSkill::writeUserSkillLevelStatus(
                        $nl["new_level_id"],
                        $user_id,
                        $this->survey->getRefId(),
                        $nl["tref_id"],
                        ilBasicSkill::ACHIEVED,
                        true,
                        1
                    );
                }
            }
        }
    }
}
