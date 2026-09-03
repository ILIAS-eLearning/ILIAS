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

declare(strict_types=1);

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
 * @author Alexander Killing <killing@leifos.de>
 */
class ilSurveyDataSet extends ilDataSet
{
    public function getSupportedVersions(): array
    {
        return array("5.1.0");
    }

    protected function getXmlNamespace(string $a_entity, string $a_schema_version): string
    {
        return "https://www.ilias.de/xml/Modules/Survey/" . $a_entity;
    }

    /**
     * Get field types for entity
     */
    protected function getTypes(string $a_entity, string $a_version): array
    {
        if ($a_entity === "svy_quest_skill") {
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
        if ($a_entity === "svy_skill_threshold") {
            switch ($a_version) {
                case "5.1.0":
                    return array(
                            "SurveyId" => "integer",
                            "BaseSkillId" => "integer",
                            "TrefId" => "integer",
                            "LevelId" => "integer",
                            "Threshold" => "integer"
                    );
            }
        }
        return array();
    }

    public function readData(string $a_entity, string $a_version, array $a_ids): void
    {
        $ilDB = $this->db;

        $this->data = array();

        if ($a_entity === "svy_quest_skill") {
            switch ($a_version) {
                case "5.1.0":
                    $this->getDirectDataFromQuery("SELECT * " .
                            " FROM svy_quest_skill WHERE " .
                            $ilDB->in("survey_id", $a_ids, false, "integer"));
                    break;
            }
        }

        if ($a_entity === "svy_skill_threshold") {
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
    protected function getDependencies(
        string $a_entity,
        string $a_version,
        ?array $a_rec = null,
        ?array $a_ids = null
    ): array {
        $ilDB = $this->db;

        /*switch ($a_entity)
        {
            case "svy_quest_skill":
                $deps["svy_skill_treshold"]["ids"][] = $a_ids;
                return $deps;
        }*/

        return [];
    }

    public function importRecord(
        string $a_entity,
        array $a_types,
        array $a_rec,
        ilImportMapping $a_mapping,
        string $a_schema_version
    ): void {
        $a_rec = $this->stripTags($a_rec);
        switch ($a_entity) {
            case "svy_quest_skill":
                $skill_data = ilBasicSkill::getCommonSkillIdForImportId(
                    (int) $this->getCurrentInstallationId(),
                    (int) $a_rec["BaseSkillId"],
                    (int) $a_rec["TrefId"]
                );
                $q_id = (int) $a_mapping->getMapping(
                    "components/ILIAS/Survey",
                    "svy_q",
                    (string) $a_rec["QId"]
                );
                if ($q_id > 0 && count($skill_data) > 0) {
                    $skill_survey = new ilSurveySkill($this->getImport()->getSurvey());
                    $skill_survey->addQuestionSkillAssignment(
                        $q_id,
                        (int) $skill_data[0]["skill_id"],
                        (int) $skill_data[0]["tref_id"]
                    );
                }
                break;

            case "svy_skill_threshold":
                $l = ilBasicSkill::getLevelIdForImportIdMatchSkill(
                    (int) $this->getCurrentInstallationId(),
                    (int) $a_rec["LevelId"],
                    (int) $a_rec["BaseSkillId"],
                    (int) $a_rec["TrefId"]
                );
                if (count($l) > 0) {
                    $skill_thres = new ilSurveySkillThresholds($this->getImport()->getSurvey());
                    $skill_thres->writeThreshold(
                        (int) $l[0]["skill_id"],
                        (int) $l[0]["tref_id"],
                        (int) $l[0]["level_id"],
                        (int) ($a_rec["Threshold"] ?? $a_rec["Treshold"])
                    );
                }
                break;
        }
    }
}
