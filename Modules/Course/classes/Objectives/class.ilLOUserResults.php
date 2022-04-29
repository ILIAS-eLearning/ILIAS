<?php declare(strict_types=0);
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
 * LO courses user results
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @package ModulesCourse
 */
class ilLOUserResults
{
    protected int $course_obj_id;
    protected int $user_id;

    public const TYPE_INITIAL = 1;
    public const TYPE_QUALIFIED = 2;

    public const STATUS_COMPLETED = 1;
    public const STATUS_FAILED = 2;

    protected ilDBInterface $db;

    public function __construct(int $a_course_obj_id, int $a_user_id)
    {
        global $DIC;

        $this->course_obj_id = $a_course_obj_id;
        $this->user_id = $a_user_id;

        $this->db = $DIC->database();
    }

    public static function lookupResult(
        int $a_course_obj_id,
        int $a_user_id,
        int $a_objective_id,
        int $a_tst_type
    ) : array {
        global $DIC;

        $ilDB = $DIC->database();
        $query = 'SELECT * FROM loc_user_results ' .
            'WHERE user_id = ' . $ilDB->quote($a_user_id, 'integer') . ' ' .
            'AND course_id = ' . $ilDB->quote($a_course_obj_id, 'integer') . ' ' .
            'AND objective_id = ' . $ilDB->quote($a_objective_id, 'integer') . ' ' .
            'AND type = ' . $ilDB->quote($a_tst_type, 'integer');
        $res = $ilDB->query($query);
        $ur = array(
            'status' => self::STATUS_FAILED,
            'result_perc' => 0,
            'limit_perc' => 0,
            'tries' => 0,
            'is_final' => 0,
            'has_result' => false
        );
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ur['status'] = $row->status;
            $ur['result_perc'] = $row->result_perc;
            $ur['limit_perc'] = $row->limit_perc;
            $ur['tries'] = $row->tries;
            $ur['is_final'] = $row->is_final;
            $ur['has_result'] = true;
        }
        return $ur;
    }

    public static function resetFinalByObjective(int $a_objective_id) : void
    {
        global $DIC;

        $db = $DIC->database();
        $query = 'UPDATE loc_user_results ' .
            'SET is_final = ' . $db->quote(0, 'integer') . ' ' .
            'WHERE objective_id = ' . $db->quote($a_objective_id, 'integer');
        $db->manipulate($query);
    }

    protected static function isValidType(int $a_type) : bool
    {
        return in_array($a_type, array(self::TYPE_INITIAL, self::TYPE_QUALIFIED));
    }

    protected static function isValidStatus(int $a_status) : bool
    {
        return in_array($a_status, array(self::STATUS_COMPLETED, self::STATUS_FAILED));
    }

    public static function deleteResultsForUser(int $a_user_id) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();
        if (!$a_user_id) {
            return false;
        }

        $ilDB->manipulate("DELETE FROM loc_user_results" .
            " WHERE user_id = " . $ilDB->quote($a_user_id, "integer"));
        return true;
    }

    public static function deleteResultsForCourse(int $a_course_id) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();
        if (!$a_course_id) {
            return false;
        }
        $ilDB->manipulate("DELETE FROM loc_user_results" .
            " WHERE course_id = " . $ilDB->quote($a_course_id, "integer"));
        return true;
    }

    public function delete() : void
    {
        $query = 'DELETE FROM loc_user_results ' .
            'WHERE course_id = ' . $this->db->quote($this->course_obj_id, ilDBConstants::T_INTEGER) . ' ' .
            'AND user_id = ' . $this->db->quote($this->user_id, ilDBConstants::T_INTEGER);
        $this->db->manipulate($query);
    }

    public static function deleteResultsFromLP(
        int $a_course_id,
        array $a_user_ids,
        bool $a_remove_initial,
        bool $a_remove_qualified,
        array $a_objective_ids
    ) : bool {
        global $DIC;

        $ilDB = $DIC->database();
        if (!$a_course_id ||
            $a_user_ids === []) {
            return false;
        }

        $base_sql = "DELETE FROM loc_user_results" .
            " WHERE course_id = " . $ilDB->quote($a_course_id, "integer") .
            " AND " . $ilDB->in("user_id", $a_user_ids, false, "integer");

        $sql = '';
        if ($a_remove_initial) {
            $sql = $base_sql .
                " AND type = " . $ilDB->quote(self::TYPE_INITIAL, "integer");
            $ilDB->manipulate($sql);
        }

        if ($a_remove_qualified) {
            $sql = $base_sql .
                " AND type = " . $ilDB->quote(self::TYPE_QUALIFIED, "integer");
            $ilDB->manipulate($sql);
        }

        if (is_array($a_objective_ids)) {
            $sql = $base_sql .
                " AND " . $ilDB->in("objective_id", $a_objective_ids, false, "integer");
            $ilDB->manipulate($sql);
        }

        $ilDB->manipulate($sql);
        return true;
    }

    public function saveObjectiveResult(
        int $a_objective_id,
        int $a_type,
        int $a_status,
        int $a_result_percentage,
        int $a_limit_percentage,
        int $a_tries,
        bool $a_is_final
    ) : bool {
        if (!self::isValidType($a_type) ||
            !self::isValidStatus($a_status)) {
            return false;
        }
        $this->db->replace(
            "loc_user_results",
            array(
                "course_id" => array("integer", $this->course_obj_id),
                "user_id" => array("integer", $this->user_id),
                "objective_id" => array("integer", $a_objective_id),
                "type" => array("integer", $a_type)
            ),
            array(
                "status" => array("integer", $a_status),
                "result_perc" => array("integer", $a_result_percentage),
                "limit_perc" => array("integer", $a_limit_percentage),
                "tries" => array("integer", $a_tries),
                "is_final" => array("integer", $a_is_final),
                "tstamp" => array("integer", time()),
            )
        );
        return true;
    }

    protected function findObjectiveIds(int $a_type = 0, int $a_status = 0, ?bool $a_is_final = null) : array
    {
        $res = array();
        $sql = "SELECT objective_id" .
            " FROM loc_user_results" .
            " WHERE course_id = " . $this->db->quote($this->course_obj_id, "integer") .
            " AND user_id = " . $this->db->quote($this->user_id, "integer");

        if ($this->isValidType($a_type)) {
            $sql .= " AND type = " . $this->db->quote($a_type, "integer");
        }
        if ($this->isValidStatus($a_status)) {
            $sql .= " AND status = " . $this->db->quote($a_status, "integer");
        }
        if ($a_is_final !== null) {
            $sql .= " AND is_final = " . $this->db->quote($a_is_final, "integer");
        }

        $set = $this->db->query($sql);
        while ($row = $this->db->fetchAssoc($set)) {
            $res[] = $row["objective_id"];
        }

        return $res;
    }

    public function getCompletedObjectiveIdsByType(int $a_type) : array
    {
        return $this->findObjectiveIds($a_type, self::STATUS_COMPLETED);
    }

    /**
     * Get all objectives where the user failed the initial test
     */
    public function getSuggestedObjectiveIds() : array
    {
        return $this->findObjectiveIds(self::TYPE_INITIAL, self::STATUS_FAILED);
    }

    /**
     * Get all objectives where the user completed the qualified test
     */
    public function getCompletedObjectiveIds() : array
    {
        $settings = ilLOSettings::getInstanceByObjId($this->course_obj_id);

        if (!$settings->isInitialTestQualifying() || !$settings->worksWithInitialTest()) {
            return $this->findObjectiveIds(self::TYPE_QUALIFIED, self::STATUS_COMPLETED);
        }

        // status of final final test overwrites initial qualified.
        $completed = [];
        if (
            $settings->isInitialTestQualifying() &&
            $settings->worksWithInitialTest()
        ) {
            $completed_candidates = array_unique(
                array_merge(
                    $this->findObjectiveIds(self::TYPE_INITIAL, self::STATUS_COMPLETED),
                    $this->findObjectiveIds(self::TYPE_QUALIFIED, self::STATUS_COMPLETED)
                )
            );
            $failed_final = $this->findObjectiveIds(self::TYPE_QUALIFIED, self::STATUS_FAILED);

            foreach ($completed_candidates as $objective_completed) {
                if (!in_array($objective_completed, $failed_final)) {
                    $completed[] = $objective_completed;
                }
            }
            return $completed;
        }
        return [];
    }

    public function getFailedObjectiveIds(bool $a_is_final = true) : array
    {
        return $this->findObjectiveIds(self::TYPE_QUALIFIED, self::STATUS_FAILED, $a_is_final);
    }

    public function getCourseResultsForUserPresentation() : array
    {
        $res = [];
        $settings = ilLOSettings::getInstanceByObjId($this->course_obj_id);

        $set = $this->db->query("SELECT *" .
            " FROM loc_user_results" .
            " WHERE course_id = " . $this->db->quote($this->course_obj_id, "integer") .
            " AND user_id = " . $this->db->quote($this->user_id, "integer"));
        while ($row = $this->db->fetchAssoc($set)) {
            // do not read initial test results, if disabled.
            if (
                $row['type'] == self::TYPE_INITIAL &&
                !$settings->worksWithInitialTest()
            ) {
                continue;
            }

            $objective_id = (int) $row["objective_id"];
            $type = (int) $row["type"];
            unset($row["objective_id"]);
            unset($row["type"]);
            $res[$objective_id][$type] = $row;
        }
        return $res;
    }

    /**
     * @return int[]
     */
    public static function getObjectiveStatusForLP(int $a_user_id, int $a_obj_id, array $a_objective_ids) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        // are initital test(s) qualifying?
        $lo_set = ilLOSettings::getInstanceByObjId($a_obj_id);
        $initial_qualifying = $lo_set->isInitialTestQualifying();

        // this method returns LP status codes!

        $res = array();

        $sql = "SELECT lor.objective_id, lor.user_id, lor.status, lor.is_final" .
            " FROM loc_user_results lor" .
            " JOIN crs_objectives cobj ON (cobj.objective_id = lor.objective_id)" .
            " WHERE " . $ilDB->in("lor.objective_id", $a_objective_ids, false, "integer");
        if (!$initial_qualifying) {
            $sql .= " AND lor.type = " . $ilDB->quote(self::TYPE_QUALIFIED, "integer");
        }
        $sql .= " AND lor.user_id = " . $ilDB->quote($a_user_id, "integer") .
            " AND cobj.active = " . $ilDB->quote(1, "integer") .
            " ORDER BY lor.type"; // qualified must come last!
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            switch ($row["status"]) {
                case self::STATUS_FAILED:
                    if ($row["is_final"]) {
                        $status = ilLPStatus::LP_STATUS_FAILED_NUM;
                    } else {
                        // #15379
                        $status = ilLPStatus::LP_STATUS_IN_PROGRESS_NUM;
                    }
                    break;

                case self::STATUS_COMPLETED:
                    $status = ilLPStatus::LP_STATUS_COMPLETED_NUM;
                    break;

                default:
                    continue 2;
            }

            // if both initial and qualified, qualified will overwrite initial
            $res[(int) $row["objective_id"]] = $status;
        }
        return $res;
    }

    /**
     * @return ?int | int[]
     */
    public static function getSummarizedObjectiveStatusForLP(
        int $a_obj_id,
        array $a_objective_ids,
        int $a_user_id = 0
    ) {
        global $DIC;

        $ilDB = $DIC->database();
        // change event is NOT parsed here!
        // are initital test(s) qualifying?
        $lo_set = ilLOSettings::getInstanceByObjId($a_obj_id);
        $initial_qualifying = $lo_set->isInitialTestQualifying();

        // this method returns LP status codes!

        $res = $tmp_completed = array();

        $sql = "SELECT lor.objective_id, lor.user_id, lor.status, lor.type, lor.is_final" .
            " FROM loc_user_results lor" .
            " JOIN crs_objectives cobj ON (cobj.objective_id = lor.objective_id)" .
            " WHERE " . $ilDB->in("lor.objective_id", $a_objective_ids, false, "integer") .
            " AND cobj.active = " . $ilDB->quote(1, "integer");
        if (!$initial_qualifying) {
            $sql .= " AND lor.type = " . $ilDB->quote(self::TYPE_QUALIFIED, "integer");
        }
        if ($a_user_id) {
            $sql .= " AND lor.user_id = " . $ilDB->quote($a_user_id, "integer");
        }
        $sql .= " ORDER BY lor.type DESC"; // qualified must come first!
        $set = $ilDB->query($sql);

        $has_final_result = array();
        while ($row = $ilDB->fetchAssoc($set)) {
            if ($row['type'] == self::TYPE_QUALIFIED) {
                $has_final_result[$row['objective_id']] = $row['user_id'];
            }

            $user_id = (int) $row["user_id"];
            $status = (int) $row["status"];

            // initial tests only count if no qualified test
            if (
                $row["type"] == self::TYPE_INITIAL &&
                in_array($row['user_id'], (array) $has_final_result[(int) $row['objective_id']])
            ) {
                continue;
            }

            // user did do something
            $res[$user_id] = ilLPStatus::LP_STATUS_IN_PROGRESS_NUM;

            switch ($status) {
                case self::STATUS_COMPLETED:
                    $tmp_completed[$user_id]++;
                    break;

                case self::STATUS_FAILED:
                    if ($row["is_final"]) {
                        // object is failed when at least 1 objective is failed without any tries left
                        $res[$user_id] = ilLPStatus::LP_STATUS_FAILED_NUM;
                    }
                    break;
            }
        }

        $all_nr = count($a_objective_ids);
        foreach ($tmp_completed as $user_id => $counter) {
            // if used as precondition object should be completed ASAP, status can be lost on subsequent tries
            if ($counter == $all_nr) {
                $res[$user_id] = ilLPStatus::LP_STATUS_COMPLETED_NUM;
            }
        }

        if ($a_user_id) {
            return isset($res[$a_user_id]) ? (int) $res[$a_user_id] : null;
        } else {
            return $res;
        }
    }

    public static function hasResults(int $a_container_id, int $a_user_id) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = 'SELECT objective_id FROM loc_user_results ' .
            'WHERE course_id = ' . $ilDB->quote($a_container_id, 'integer') . ' ' .
            'AND user_id = ' . $ilDB->quote($a_user_id, 'integer');

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return true;
        }
        return false;
    }

    /**
     * Get completed learning objectives for user and time frame
     */
    public static function getCompletionsOfUser(int $a_user_id, int $a_from_ts, int $a_to_ts) : array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $res = [];
        $sql = "SELECT lor.objective_id, lor.user_id, lor.status, lor.is_final, lor.tstamp, lor.course_id, cobj.title" .
            " FROM loc_user_results lor" .
            " JOIN crs_objectives cobj ON (cobj.objective_id = lor.objective_id)" .
            " WHERE lor.user_id = " . $ilDB->quote($a_user_id, "integer") .
            " AND lor.type = " . $ilDB->quote(self::TYPE_QUALIFIED, "integer") .
            " AND lor.tstamp >= " . $ilDB->quote($a_from_ts, "integer") .
            " AND lor.tstamp <= " . $ilDB->quote($a_to_ts, "integer") .
            " AND lor.status = " . $ilDB->quote(self::STATUS_COMPLETED, "integer");

        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[(int) $row["objective_id"]] = $row;
        }
        return $res;
    }
}
