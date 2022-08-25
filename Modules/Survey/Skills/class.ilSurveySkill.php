<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Skill/Competence handling in surveys
 * @author Alexander Killing <killing@leifos.de>
 */
class ilSurveySkill
{
    protected ilObjSurvey $survey;
    protected ilDBInterface $db;

    /**
     * @var array<int, array{q_id: int, base_skill_id: int, tref_id: int}>
     */
    protected array $q_skill = array();
    protected ilLogger $log;
    protected \ILIAS\Skill\Service\SkillProfileService $skill_profile_service;

    public function __construct(ilObjSurvey $a_survey)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->survey = $a_survey;
        $this->read();
        $this->log = ilLoggerFactory::getLogger("svy");
        $this->skill_profile_service = $DIC->skills()->profile();
    }

    public function read(): void
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT * FROM svy_quest_skill " .
            " WHERE survey_id = " . $ilDB->quote($this->survey->getId(), "integer")
        );

        while ($rec = $ilDB->fetchAssoc($set)) {
            if (SurveyQuestion::_questionExists($rec["q_id"])) {
                $this->q_skill[(int) $rec["q_id"]] = array(
                    "q_id" => (int) $rec["q_id"],
                    "base_skill_id" => (int) $rec["base_skill_id"],
                    "tref_id" => (int) $rec["tref_id"]
                );
            }
        }
    }

    /**
     * Get skill for question
     * @param int $a_question_id question id
     * @return ?array{q_id: int, base_skill_id: int, tref_id: int} skill array
     */
    public function getSkillForQuestion(
        int $a_question_id
    ): ?array {
        return $this->q_skill[$a_question_id] ?? null;
    }

    /**
     * Get questions for skill
     * @return int[]
     */
    public function getQuestionsForSkill(
        int $a_base_skill_id,
        int $a_tref_id
    ): array {
        $q_ids = array();
        foreach ($this->q_skill as $q_id => $s) {
            if ($s["base_skill_id"] === $a_base_skill_id &&
                $s["tref_id"] === $a_tref_id) {
                $q_ids[] = $q_id;
            }
        }
        return $q_ids;
    }


    /**
     * Add survey question to skill assignment
     * @param int $a_question_id question id
     * @param int $a_base_skill_id base skill id
     * @param int $a_tref_id skill template reference id (0, if no template involved)
     */
    public function addQuestionSkillAssignment(
        int $a_question_id,
        int $a_base_skill_id,
        int $a_tref_id
    ): void {
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
        $this->q_skill[$a_question_id] = array(
            "q_id" => $a_question_id,
            "base_skill_id" => $a_base_skill_id,
            "tref_id" => $a_tref_id
        );

        // add usage
        ilSkillUsage::setUsage($this->survey->getId(), $a_base_skill_id, $a_tref_id);
    }

    public function removeQuestionSkillAssignment(
        int $a_question_id
    ): void {
        $ilDB = $this->db;

        // read skills that are assigned to the quesiton
        $set = $ilDB->query(
            "SELECT * FROM svy_quest_skill " .
            " WHERE q_id = " . $ilDB->quote($a_question_id, "integer")
        );
        $skills = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $skills[] = array(
                "skill_id" => $rec["base_skill_id"],
                "tref_id" => $rec["tref_id"]
            );
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
     */
    public static function handleQuestionDeletion(
        int $a_question_id,
        int $a_obj_id
    ): void {
        if (ilObject::_lookupType($a_obj_id) === "svy") {
            // mantis 11691
            $svy = new ilObjSurvey($a_obj_id, false);
            $svy_skill = new ilSurveySkill($svy);
            $svy_skill->removeQuestionSkillAssignment($a_question_id);
        }
    }

    /**
     * Remove usages of skills
     * This function checks, if the skills are really not in use anymore
     * @param array $a_skills array of arrays with keys "skill_id" and "tref_id"
     */
    public function removeUsagesOfSkills(
        array $a_skills
    ): void {
        $used_skills = array();
        foreach ($a_skills as $skill) {
            if ($this->isSkillAssignedToQuestion($skill["skill_id"], $skill["tref_id"])) {
                $used_skills[] = $skill["skill_id"] . ":" . $skill["tref_id"];
            }
        }
        reset($a_skills);

        // now remove all usages that have been confirmed
        foreach ($a_skills as $skill) {
            if (!in_array($skill["skill_id"] . ":" . $skill["tref_id"], $used_skills, true)) {
                ilSkillUsage::setUsage($this->survey->getId(), $skill["skill_id"], $skill["tref_id"], false);
            }
        }
    }

    public function isSkillAssignedToQuestion(
        int $a_skill_id,
        int $a_tref_id
    ): bool {
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
     * @return array skill array
     */
    public function getAllAssignedSkillsAsOptions(): array
    {
        $skills = array();
        foreach ($this->q_skill as $sk) {
            $skills[$sk["base_skill_id"] . ":" . $sk["tref_id"]] = ilBasicSkill::_lookupTitle($sk["base_skill_id"]);
        }
        return $skills;
    }

    /**
     * Determine skill levels for appraisee
     * @return array array with lots of information
     * @todo introduce dto
     */
    public function determineSkillLevelsForAppraisee(
        int $a_appraisee_id,
        bool $a_self_eval = false,
        int $finished_id = 0
    ): array {
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

        $finished_ids = [];
        if (!$a_self_eval) {
            if ($finished_id > 0) {
                $finished_ids = array($finished_id);
            } else {
                $finished_ids = $this->survey->getFinishedIdsForAppraiseeId($a_appraisee_id, true);
            }
        } else {
            $finished_id = $this->survey->getFinishedIdForAppraiseeIdAndRaterId($a_appraisee_id, $a_appraisee_id);
            if ($finished_id > 0) {
                $finished_ids = array($finished_id);
            }
        }

        /* ???
        if (!is_array($finished_ids)) {
            $finished_ids = array(-1);
        }*/

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
                        $cnt += count($scale_values); // nr of answers (always one in the case of single choice)
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

            $skthr = new ilSurveySkillThresholds($this->survey);
            $thresholds = $skthr->getThresholds();
            $previous = 0;
            $previous_t = 0;
            foreach ($skills[$k]["level_data"] as $l) {
                $t = $thresholds[$l["id"]][$s["tref_id"]];
                if ($t > 0 && $mean_sum >= $t) {
                    $skills[$k]["new_level"] = $l["title"];
                    $skills[$k]["new_level_id"] = $l["id"];
                    $skills[$k]["next_level_perc"] = 0;
                } elseif ($t > 0 && $mean_sum < $t) {
                    // first unfulfilled level
                    if ($previous == $skills[$k]["new_level_id"] && !isset($skills[$k]["next_level_perc"])) {
                        $skills[$k]["next_level_perc"] = 1 / ($t - $previous_t) * ($mean_sum - $previous_t);
                    }
                }
                if ($t > 0) {
                    $previous = $l["id"];
                    $previous_t = $t;
                }
            }
        }
        return $skills;
    }

    public function determineMaxScale(
        int $a_base_skill,
        int $a_tref_id = 0
    ): int {
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
     * Write appraisee skills and add them to user's competence records
     */
    public function writeAndAddAppraiseeSkills(
        int $user_id
    ): void {
        // write raters evaluation
        $new_levels = $this->determineSkillLevelsForAppraisee($user_id);
        foreach ($new_levels as $nl) {
            if ($nl["new_level_id"] > 0) {
                ilBasicSkill::writeUserSkillLevelStatus(
                    $nl["new_level_id"],
                    $user_id,
                    $this->survey->getRefId(),
                    $nl["tref_id"],
                    ilBasicSkill::ACHIEVED,
                    true,
                    false,
                    "",
                    $nl["next_level_perc"]
                );

                if ($nl["tref_id"] > 0) {
                    ilPersonalSkill::addPersonalSkill($user_id, $nl["tref_id"]);
                } else {
                    ilPersonalSkill::addPersonalSkill($user_id, $nl["base_skill_id"]);
                }
            }
        }

        //write profile completion entries if fulfilment status has changed
        $this->skill_profile_service->writeCompletionEntryForAllProfiles($user_id);

        // write self evaluation
        $this->writeAndAddSelfEvalSkills($user_id);
    }

    public function writeAndAddIndFeedbackSkills(
        int $finished_id,
        int $appr_id,
        string $rater_id
    ): void {
        $new_levels = $this->determineSkillLevelsForAppraisee($appr_id, false, $finished_id);
        foreach ($new_levels as $nl) {
            if ($nl["new_level_id"] > 0) {
                ilBasicSkill::writeUserSkillLevelStatus(
                    $nl["new_level_id"],
                    $appr_id,
                    $this->survey->getRefId(),
                    $nl["tref_id"],
                    ilBasicSkill::ACHIEVED,
                    true,
                    false,
                    "",
                    $nl["next_level_perc"],
                    $rater_id
                );

                if ($nl["tref_id"] > 0) {
                    ilPersonalSkill::addPersonalSkill($appr_id, $nl["tref_id"]);
                } else {
                    ilPersonalSkill::addPersonalSkill($appr_id, $nl["base_skill_id"]);
                }
            }
        }
    }

    /**
     * Write skills on self evaluation and add them to user's competence records
     */
    public function writeAndAddSelfEvalSkills(
        int $user_id
    ): void {
        if ($user_id > 0 && in_array($this->survey->getMode(), [ilObjSurvey::MODE_SELF_EVAL, ilObjSurvey::MODE_360], true)) {
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
                        1,
                        "",
                        $nl["next_level_perc"]
                    );

                    if ($nl["tref_id"] > 0) {
                        ilPersonalSkill::addPersonalSkill($user_id, $nl["tref_id"]);
                    } else {
                        ilPersonalSkill::addPersonalSkill($user_id, $nl["base_skill_id"]);
                    }
                }
            }
        }
    }
}
