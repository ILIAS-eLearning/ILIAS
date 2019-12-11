<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Survey\Execution;

/**
 * DB repo for survey run. Table svy_finished.
 *
 * Please note that there are lots of accesses to svy_finished
 * in other classes.
 *
 * @author killing@leifos.de
 */
class RunDBRepository
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
     * Get all finished surveys of a user
     *
     * @param int $user_id user id
     * @return int[] survey ids
     */
    public function getFinishedSurveysOfUser(int $user_id) : array
    {
        $db = $this->db;

        $set = $db->queryF(
            "SELECT survey_fi FROM svy_finished " .
            " WHERE user_fi = %s AND state = %s",
            ["integer", "integer"],
            [$user_id, 1]
        );
        $items = [];
        while ($rec = $db->fetchAssoc($set)) {
            $items[] = (int) $rec["survey_fi"];
        }
        return $items;
    }

    /**
     * Get all unfinished surveys of a user
     *
     * @param int $user_id user id
     * @return int[] survey ids
     */
    public function getUnfinishedSurveysOfUser(int $user_id) : array
    {
        $db = $this->db;

        $set = $db->queryF(
            "SELECT survey_fi FROM svy_finished " .
            " WHERE user_fi = %s AND state = %s",
            ["integer", "integer"],
            [$user_id, 0]
        );
        $items = [];
        while ($rec = $db->fetchAssoc($set)) {
            $items[] = $rec["survey_fi"];
        }
        return $items;
    }

    /**
     * Get finished appraisees for a rater
     *
     * @param int $rater_id
     * @return array
     */
    public function getFinishedAppraiseesForRater(int $rater_id) : array
    {
        $db = $this->db;

        $set = $db->queryF(
            "SELECT survey_fi, appr_id FROM svy_finished " .
            " WHERE user_fi = %s AND state = %s",
            ["integer", "integer"],
            [$rater_id, 1]
        );
        $appraisee = [];
        while ($rec = $db->fetchAssoc($set)) {
            $appraisee[] = [
                "survey_id" => $rec["survey_fi"],
                "appr_id" => $rec["appr_id"]
            ];
        }
        return $appraisee;
    }
}
