<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/DataSet/classes/class.ilDataSet.php");

/**
 * Survey Data set class
 *
 * Currently most of the survey export is still done "old school".
 *
 * The dataset part implements mostly the 360 extension:
 *
 * - svy_quest_skill: question to skill assignment
 * - svy_skill_threshold: skill threshold values
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ingroup ModulesSurvey
 */
class ilSurveyDataSet extends ilDataSet
{
    /**
     * Get supported versions
     *
     * @return array of version strings
     */
    public function getSupportedVersions()
    {
        return array("5.1.0");
    }
    
    /**
     * Get xml namespace
     *
     * @param
     * @return
     */
    public function getXmlNamespace($a_entity, $a_schema_version)
    {
        return "http://www.ilias.de/xml/Modules/Survey/" . $a_entity;
    }
    
    /**
     * Get field types for entity
     *
     * @param string $a_entity entity
     * @param string $a_version version
     * @return array
     */
    protected function getTypes($a_entity, $a_version)
    {
        if ($a_entity == "svy_quest_skill") {
            switch ($a_version) {
                case "5.1.0":
                    return array(
                            "QId" => "integer",
                            "SurveyId" => "integer",
                            "BaseSkillId" => "integer",
                            "TrefId" => "integer"
                    );
            }
        }
        if ($a_entity == "svy_skill_threshold") {
            switch ($a_version) {
                case "5.1.0":
                    return array(
                            "SurveyId" => "integer",
                            "BaseSkillId" => "integer",
                            "TrefId" => "integer",
                            "LevelId" => "integer",
                            "Treshold" => "integer"
                    );
            }
        }
        return array();
    }

    /**
     * Read data
     *
     * @param string $a_entity entity
     * @param string $a_version version
     * @param array $a_ids ids
     * @param string $a_field field
     */
    public function readData($a_entity, $a_version, $a_ids, $a_field = "")
    {
        $ilDB = $this->db;

        $this->data = array();

        if (!is_array($a_ids)) {
            $a_ids = array($a_ids);
        }

        if ($a_entity == "svy_quest_skill") {
            switch ($a_version) {
                case "5.1.0":
                    $this->getDirectDataFromQuery("SELECT * " .
                            " FROM svy_quest_skill WHERE " .
                            $ilDB->in("survey_id", $a_ids, false, "integer"));
                    break;

            }
        }

        if ($a_entity == "svy_skill_threshold") {
            switch ($a_version) {
                case "5.1.0":
                    $this->getDirectDataFromQuery("SELECT * " .
                            " FROM svy_skill_threshold WHERE " .
                            $ilDB->in("survey_id", $a_ids, false, "integer"));
                    break;

            }
        }
    }
    
    /**
     * Determine the dependent sets of data
     */
    protected function getDependencies($a_entity, $a_version, $a_rec, $a_ids)
    {
        $ilDB = $this->db;

        /*switch ($a_entity)
        {
            case "svy_quest_skill":
                $deps["svy_skill_treshold"]["ids"][] = $a_ids;
                return $deps;
        }*/

        return false;
    }
    
    
    /**
     * Import record
     *
     * @param
     * @return
     */
    public function importRecord($a_entity, $a_types, $a_rec, $a_mapping, $a_schema_version)
    {
        switch ($a_entity) {
            case "svy_quest_skill":
                include_once("./Services/Skill/classes/class.ilBasicSkill.php");
                $skill_data = ilBasicSkill::getCommonSkillIdForImportId($this->getCurrentInstallationId(), $a_rec["BaseSkillId"], $a_rec["TrefId"]);
                $q_id = $a_mapping->getMapping("Modules/Survey", "svy_q", $a_rec["QId"]);
                if ($q_id > 0 && count($skill_data) > 0) {
                    include_once("./Modules/Survey/classes/class.ilSurveySkill.php");
                    $skill_survey = new ilSurveySkill($this->getImport()->getSurvey());
                    $skill_survey->addQuestionSkillAssignment($q_id, $skill_data[0]["skill_id"], $skill_data[0]["tref_id"]);
                }
                break;

            case "svy_skill_threshold":
                $l = ilBasicSkill::getLevelIdForImportIdMatchSkill($this->getCurrentInstallationId(), $a_rec["LevelId"], $a_rec["BaseSkillId"], $a_rec["TrefId"]);
                if (count($l) > 0) {
                    include_once("./Modules/Survey/classes/class.ilSurveySkillThresholds.php");
                    $skill_thres = new ilSurveySkillThresholds($this->getImport()->getSurvey());
                    //echo "<br>".$l[0]["skill_id"]."-".$l[0]["tref_id"]."-".$l[0]["level_id"]."-".$a_rec["Threshold"]."-".$sid."-"; exit;
                    $skill_thres->writeThreshold($l[0]["skill_id"], $l[0]["tref_id"], $l[0]["level_id"], $a_rec["Threshold"]);
                }
                break;
        }
    }
}
