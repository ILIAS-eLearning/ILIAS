<?php
declare(strict_types = 1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Survey\Execution;

use ILIAS\Survey\InternalDataService;

/**
 * DB repo for survey run. Table svy_finished.
 * Please note that there are lots of accesses to svy_finished
 * in other classes.
 * @author killing@leifos.de
 */
class RunDBRepository
{
    public const NOT_STARTED = -1;
    public const STARTED_NOT_FINISHED = 0;
    public const FINISHED = 1;

    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * @var InternalDataService
     */
    protected $data;

    /**
     * Constructor
     */
    public function __construct(
        InternalDataService $data,
        \ilDBInterface $db = null
    ) {
        global $DIC;

        $this->data = $data;
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

    /**
     * @param int    $survey_id
     * @param int    $user_id
     * @param string $code
     * @param int    $appr_id
     * @return int|null
     */
    public function getCurrentRunId(int $survey_id, int $user_id, string $code = "", int $appr_id = 0)
    {
        $db = $this->db;

        if ($code != "") { // #15031 - should not matter if code was used by registered or anonymous (each code must be unique)
            $set = $db->queryF(
                "SELECT * FROM svy_finished" .
                " WHERE survey_fi = %s AND anonymous_id = %s AND appr_id = %s",
                array('integer', 'text', 'integer'),
                array($survey_id, $code, $appr_id)
            );
        } else {
            $set = $db->queryF(
                "SELECT * FROM svy_finished" .
                " WHERE survey_fi = %s AND user_fi = %s AND appr_id = %s",
                array('integer', 'integer', 'integer'),
                array($survey_id, $user_id, $appr_id)
            );
        }
        if ($rec = $db->fetchAssoc($set)) {
            return (int) $rec["finished_id"];
        }
        return null;
    }

    public function getState(int $run_id) : int
    {
        $db = $this->db;

        $set = $db->queryF(
            "SELECT * FROM svy_finished" .
            " WHERE finished_id = %s ",
            array('integer'),
            array($run_id)
        );
        if ($rec = $db->fetchAssoc($set)) {
            return (int) $rec["state"];
        }
        return self::NOT_STARTED;
    }

    /**
     * @param int    $survey_id
     * @param int    $user_id
     * @param string $code
     * @return Run[]
     */
    public function getRunsForUser(int $survey_id, int $user_id, string $code = "") : array
    {
        $db = $this->db;

        $sql = "SELECT * FROM svy_finished" .
            " WHERE survey_fi = " . $db->quote($survey_id, "integer");
        // if proper user id is given, use it or current code
        if ($user_id != ANONYMOUS_USER_ID) {
            $sql .= " AND (user_fi = " . $db->quote($user_id, "integer");
            if ($code != "") {
                $sql .= " OR anonymous_id = " . $db->quote($code, "text");
            }
            $sql .= ")";
        }
        // use anonymous code to find finished id(s)
        else {
            if ($code == "") {
                return [];
            }
            $sql .= " AND anonymous_id = " . $db->quote($code, "text");
        }
        $set = $db->query($sql);
        $runs = [];
        while ($row = $db->fetchAssoc($set)) {
            $runs[$row["finished_id"]] = $this->data->run($survey_id, $user_id)
                ->withId((int) $row["finished_id"])
                ->withFinished((bool) $row["state"])
                ->withCode((string) $row["anonymous_id"])
                ->withTimestamp((int) $row["tstamp"])
                ->withAppraiseeId((int) $row["appr_id"])
                ->withLastPage((int) $row["lastpage"]);
        }
        return $runs;
    }

    public function getById(int $run_id) : ?Run
    {
        $db = $this->db;

        $sql = "SELECT * FROM svy_finished" .
            " WHERE finished_id = " . $db->quote($run_id, "integer");
        $set = $db->query($sql);
        while ($row = $db->fetchAssoc($set)) {
            return $this->data->run((int) $row["survey_fi"], (int) $row["user_fi"])
                ->withId((int) $row["finished_id"])
                ->withFinished((bool) $row["state"])
                ->withCode((string) $row["anonymous_id"])
                ->withTimestamp((int) $row["tstamp"])
                ->withAppraiseeId((int) $row["appr_id"])
                ->withLastPage((int) $row["lastpage"]);
        }
        return null;
    }


    /**
     * Add run
     * @param int    $survey_id
     * @param int    $user_id
     * @param string $code
     * @param int    $appraisee_id
     * @return int run id
     */
    public function add(int $survey_id, int $user_id, string $code, int $appraisee_id = 0) : int
    {
        $db = $this->db;

        $next_id = (int) $db->nextId('svy_finished');
        $affectedRows = $db->manipulateF(
            "INSERT INTO svy_finished (finished_id, survey_fi, user_fi, anonymous_id, state, tstamp, appr_id) " .
            "VALUES (%s, %s, %s, %s, %s, %s, %s)",
            array('integer','integer','integer','text','text','integer','integer'),
            array($next_id, $survey_id, $user_id, $code, 0, time(), $appraisee_id)
        );

        return $next_id;
    }
}
