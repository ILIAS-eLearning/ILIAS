<?php

/* Copyright (c) 1998- ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Survey\Survey360;

/**
 * Apraisee / Rater DB repository
 * Tables: svy_360_rater, svy_360_appr
 *
 * @author killing@leifos.de
 */
class AppraiseeDBRepository
{
    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * Constructor
     */
    public function __construct(\ilDBInterface $db = null)
    {
        global $DIC;

        $this->db = (is_null($db))
            ? $DIC->database()
            : $db;
    }

    /**
     * Get surveys for Rater
     *
     * @param int $rater_id
     * @return array
     */
    public function getAppraiseesForRater(int $rater_id) : array
    {
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
                "survey_id" => $rec["obj_id"],
                "appr_id" => $rec["appr_id"]
            ];
        }
        return $appraisee;
    }
    

    /**
     * Get closed appraisees for a number of surveys
     *
     * @param int[] $survey_ids
     * @return array
     */
    public function getClosedAppraiseesForSurveys(array $survey_ids)
    {
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
                "survey_id" => $rec["obj_id"],
                "appr_id" => $rec["user_id"]
            ];
        }
        return $closed_appraisees;
    }

    /**
     * Get all unclosed surveys of an appraisee
     *
     * @param int $appr_user_id
     * @return int[]
     */
    public function getUnclosedSurveysForAppraisee(int $appr_user_id) : array
    {
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
            $unclosed_surveys[] = $rec["obj_id"];
        }
        return $unclosed_surveys;
    }
}
