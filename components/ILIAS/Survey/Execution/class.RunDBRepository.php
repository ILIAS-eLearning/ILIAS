<?php

declare(strict_types=1);

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

namespace ILIAS\Survey\Execution;

use ILIAS\Survey\InternalDataService;

/**
 * DB repo for survey run. Table svy_finished.
 * Please note that there are lots of accesses to svy_finished
 * in other classes.
 * @author Alexander Killing <killing@leifos.de>
 */
class RunDBRepository
{
    public const NOT_STARTED = -1;
    public const STARTED_NOT_FINISHED = 0;
    public const FINISHED = 1;

    protected \ilDBInterface $db;
    protected InternalDataService $data;

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
     * @return int[] survey ids
     */
    public function getFinishedSurveysOfUser(
        int $user_id
    ): array {
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
     * @return int[] survey ids
     */
    public function getUnfinishedSurveysOfUser(
        int $user_id
    ): array {
        $db = $this->db;

        $set = $db->queryF(
            "SELECT survey_fi FROM svy_finished " .
            " WHERE user_fi = %s AND state = %s",
            ["integer", "integer"],
            [$user_id, 0]
        );
        $items = [];
        while ($rec = $db->fetchAssoc($set)) {
            $items[] = (int) $rec["survey_fi"];
        }
        return $items;
    }

    /**
     * @param int $rater_id
     * @return array{survey_id: int, appr_id: int}[]
     */
    public function getFinishedAppraiseesForRater(
        int $rater_id
    ): array {
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
                "survey_id" => (int) $rec["survey_fi"],
                "appr_id" => (int) $rec["appr_id"]
            ];
        }
        return $appraisee;
    }

    public function getCurrentRunId(
        int $survey_id,
        int $user_id,
        string $code = "",
        int $appr_id = 0
    ): ?int {
        $db = $this->db;

        if ($code !== "") { // #15031 - should not matter if code was used by registered or anonymous (each code must be unique)
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

    public function getState(
        int $run_id
    ): int {
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
     * @return Run[]
     */
    public function getRunsForUser(
        int $survey_id,
        int $user_id,
        string $code = ""
    ): array {
        $db = $this->db;

        $sql = "SELECT * FROM svy_finished" .
            " WHERE survey_fi = " . $db->quote($survey_id, "integer");
        // if proper user id is given, use it or current code
        if ($user_id !== ANONYMOUS_USER_ID) {
            $sql .= " AND (user_fi = " . $db->quote($user_id, "integer");
            if ($code !== "") {
                $sql .= " OR anonymous_id = " . $db->quote($code, "text");
            }
            $sql .= ")";
        }
        // use anonymous code to find finished id(s)
        else {
            if ($code === "") {
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

    public function getById(
        int $run_id
    ): ?Run {
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
     * Add new run
     * @return int run id
     */
    public function add(
        int $survey_id,
        int $user_id,
        string $code,
        int $appraisee_id = 0
    ): int {
        $db = $this->db;

        $next_id = $db->nextId('svy_finished');
        $affectedRows = $db->manipulateF(
            "INSERT INTO svy_finished (finished_id, survey_fi, user_fi, anonymous_id, state, tstamp, appr_id) " .
            "VALUES (%s, %s, %s, %s, %s, %s, %s)",
            array('integer','integer','integer','text','text','integer','integer'),
            array($next_id, $survey_id, $user_id, $code, 0, time(), $appraisee_id)
        );

        return $next_id;
    }

    /**
     * Add time record
     */
    public function addTime(int $run_id, int $time, int $first_question): void
    {
        $db = $this->db;
        $id = $db->nextId('svy_times');
        $db->manipulateF(
            "INSERT INTO svy_times (id, finished_fi, entered_page, left_page, first_question) VALUES (%s, %s, %s, %s,%s)",
            array('integer','integer', 'integer', 'integer', 'integer'),
            array($id, $run_id, $time, null, $first_question)
        );
    }

    public function updateTime(int $run_id, int $time, int $entered_time): void
    {
        $db = $this->db;
        $db->manipulateF(
            "UPDATE svy_times SET left_page = %s WHERE finished_fi = %s AND entered_page = %s",
            array('integer', 'integer', 'integer'),
            array($time, $run_id, $entered_time)
        );
    }
}
