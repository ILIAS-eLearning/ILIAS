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
 * Skill tresholds for 360 surveys
 * @author Alexander Killing <killing@leifos.de>
 */
class ilSurveySkillThresholds
{
    protected ilObjSurvey $survey;
    protected ilDBInterface $db;
    /** @var array<int, array<int, int>>  */
    protected array $threshold;

    public function __construct(ilObjSurvey $a_survey)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->survey = $a_survey;
        $this->read();
    }

    public function read(): void
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT * FROM svy_skill_threshold " .
            " WHERE survey_id = " . $ilDB->quote($this->survey->getId(), "integer")
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $this->threshold[(int) $rec['level_id']][(int) $rec['tref_id']] = (int) $rec['threshold'];
        }
    }

    /**
     * @return array<int, array<int, int>>
     */
    public function getThresholds(): array
    {
        return $this->threshold;
    }

    public function writeThreshold(
        int $a_base_skill_id,
        int $a_tref_id,
        int $a_level_id,
        int $a_threshold
    ): void {
        $ilDB = $this->db;

        $ilDB->replace(
            "svy_skill_threshold",
            array("survey_id" => array("integer", $this->survey->getId()),
                  "base_skill_id" => array("integer", $a_base_skill_id),
                  "tref_id" => array("integer", $a_tref_id),
                  "level_id" => array("integer", $a_level_id)
                ),
            array("threshold" => array("integer", $a_threshold))
        );
    }
}
