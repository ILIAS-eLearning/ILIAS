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

namespace ILIAS\Survey\Survey360;

/**
 * Apraisee / Rater DB repository
 * Tables: svy_360_rater, svy_360_appr
 * @author Alexander Killing <killing@leifos.de>
 */
class AppraiseeDBRepository
{
    protected \ilDBInterface $db;

    public function __construct(
        \ilDBInterface $db = null
    ) {
        global $DIC;

        $this->db = (is_null($db))
            ? $DIC->database()
            : $db;
    }

    /**
     * @param int $rater_id
     * @return @return array{survey_id: int, appr_id: int}[]
     */
    public function getAppraiseesForRater(
        int $rater_id
    ) : array {
        $db = $this->db;
        
        $set = $db->queryF(
            "SELECT obj_id, appr_id FROM svy_360_rater " .
            " WHERE user_id = %s ",
            ["integer"],
            [$rater_id]
        );
        $appraisee = [];
        while ($rec = $db->fetchAssoc($set)) {
            $appraisee[] = [
                "survey_id" => (int) $rec["obj_id"],
                "appr_id" => (int) $rec["appr_id"]
            ];
        }
        return $appraisee;
    }
    

    /**
     * Get closed appraisees for a number of surveys
     * @param int[] $survey_ids
     * @return array{survey_id: int, appr_id: int}[]
     */
    public function getClosedAppraiseesForSurveys(
        array $survey_ids
    ) : array {
        $db = $this->db;
        
        $set = $db->queryF(
            "SELECT obj_id, user_id FROM svy_360_appr " .
            " WHERE " . $db->in("obj_id", $survey_ids, false, "integer") .
            "AND has_closed = %s",
            ["integer"],
            [1]
        );
        $closed_appraisees = [];
        while ($rec = $db->fetchAssoc($set)) {
            $closed_appraisees[] = [
                "survey_id" => (int) $rec["obj_id"],
                "appr_id" => (int) $rec["user_id"]
            ];
        }
        return $closed_appraisees;
    }

    /**
     * Get all unclosed surveys of an appraisee
     * @return int[]
     */
    public function getUnclosedSurveysForAppraisee(
        int $appr_user_id
    ) : array {
        $db = $this->db;

        $set = $db->queryF(
            "SELECT DISTINCT obj_id FROM svy_360_appr " .
            "WHERE user_id = %s " .
            "AND has_closed = %s",
            ["integer", "integer"],
            [$appr_user_id, 0]
        );
        $unclosed_surveys = [];
        while ($rec = $db->fetchAssoc($set)) {
            $unclosed_surveys[] = (int) $rec["obj_id"];
        }
        return $unclosed_surveys;
    }
}
