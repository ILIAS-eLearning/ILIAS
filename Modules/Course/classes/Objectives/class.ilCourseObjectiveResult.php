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
 * class ilcourseobjective
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilCourseObjectiveResult
{
    public const IL_OBJECTIVE_STATUS_EMPTY = 'empty';
    public const IL_OBJECTIVE_STATUS_PRETEST = 'pretest';
    public const IL_OBJECTIVE_STATUS_FINAL = 'final';
    public const IL_OBJECTIVE_STATUS_NONE = 'none';
    public const IL_OBJECTIVE_STATUS_FINISHED = 'finished';
    public const IL_OBJECTIVE_STATUS_PRETEST_NON_SUGGEST = 'pretest_non_suggest';

    private int $user_id;

    protected ilDBInterface $db;

    public function __construct(int $a_usr_id)
    {
        global $DIC;

        $this->db = $DIC->database();

        $this->user_id = $a_usr_id;
    }

    public function getUserId() : int
    {
        return $this->user_id;
    }

    public function getAccomplished(int $a_crs_id) : array
    {
        return ilCourseObjectiveResult::_getAccomplished($this->getUserId(), $a_crs_id);
    }

    public static function _getAccomplished(int $a_user_id, int $a_crs_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $objectives = ilCourseObjective::_getObjectiveIds($a_crs_id, true);
        if (!is_array($objectives)) {
            return array();
        }
        $query = "SELECT objective_id FROM crs_objective_status " .
            "WHERE " . $ilDB->in('objective_id', $objectives, false, 'integer') . ' ' .
            "AND user_id = " . $ilDB->quote($a_user_id, 'integer') . " ";
        $res = $ilDB->query($query);
        $accomplished = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $accomplished[] = (int) $row->objective_id;
        }
        return $accomplished;
    }

    public function getSuggested(int $a_crs_id, string $a_status = self::IL_OBJECTIVE_STATUS_FINAL) : array
    {
        return ilCourseObjectiveResult::_getSuggested($this->getUserId(), $a_crs_id, $a_status);
    }

    public static function _getSuggested(
        int $a_user_id,
        int $a_crs_id,
        string $a_status = self::IL_OBJECTIVE_STATUS_FINAL
    ) : array {
        global $DIC;

        $ilDB = $DIC->database();

        $objectives = ilCourseObjective::_getObjectiveIds($a_crs_id, true);
        $finished = $suggested = [];
        if (
            $a_status == self::IL_OBJECTIVE_STATUS_FINAL ||
            $a_status == self::IL_OBJECTIVE_STATUS_FINISHED
        ) {
            // check finished
            $query = "SELECT objective_id FROM crs_objective_status " .
                "WHERE " . $ilDB->in('objective_id', $objectives, false, 'integer') . " " .
                "AND user_id = " . $ilDB->quote($a_user_id, 'integer') . " ";
            $res = $ilDB->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $finished[] = (int) $row->objective_id;
            }
        } else {
            // Pretest
            $query = "SELECT objective_id FROM crs_objective_status_p " .
                "WHERE " . $ilDB->in('objective_id', $objectives, false, 'integer') . ' ' .
                "AND user_id = " . $ilDB->quote($a_user_id, 'integer');
            $res = $ilDB->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $finished[] = (int) $row->objective_id;
            }
        }
        foreach ($objectives as $objective_id) {
            if (!in_array($objective_id, $finished)) {
                $suggested[] = $objective_id;
            }
        }
        return $suggested;
    }

    public static function getSuggestedQuestions(int $a_usr_id, int $a_crs_id) : array
    {
        $qsts = [];
        foreach (self::_getSuggested($a_usr_id, $a_crs_id) as $objective_id) {
            $obj = new ilCourseObjectiveQuestion($objective_id);
            foreach ($obj->getFinalTestQuestions() as $qst) {
                $qsts[] = $qst['question_id'];
            }
        }
        return $qsts;
    }

    protected function resetTestForUser(ilObjTest $a_test, int $a_user_id) : void
    {
        // #15038
        $test_lp = ilTestLP::getInstance($a_test->getId());
        $test_lp->resetLPDataForUserIds(array($a_user_id));

        // #15205 - see ilObjTestGUI::confirmDeleteSelectedUserDataObject()
        $active_id = $a_test->getActiveIdOfUser($a_user_id);
        if ($active_id) {
            $a_test->removeTestActives(array($active_id));
        }
    }

    public function reset(int $a_course_id) : void
    {
        $assignments = ilLOTestAssignments::getInstance($a_course_id);
        foreach (array_merge(
            $assignments->getAssignmentsByType(ilLOSettings::TYPE_TEST_INITIAL),
            $assignments->getAssignmentsByType(ilLOSettings::TYPE_TEST_QUALIFIED)
        )
                 as $assignment) {
            $tst = ilObjectFactory::getInstanceByRefId($assignment->getTestRefId(), false);
            if ($tst instanceof ilObjTest) {
                global $DIC;

                $lng = $DIC['lng'];

                $participantData = new ilTestParticipantData($this->db, $lng);
                $participantData->setUserIdsFilter(array($this->getUserId()));
                $participantData->load($tst->getTestId());
                $tst->removeTestResults($participantData);
            }
        }

        $initial = ilLOSettings::getInstanceByObjId($a_course_id)->getInitialTest();
        $initial_tst = ilObjectFactory::getInstanceByRefId($initial, false);
        if ($initial_tst instanceof ilObjTest) {
            $this->resetTestForUser($initial_tst, $this->getUserId());
        }

        $qualified = ilLOSettings::getInstanceByObjId($a_course_id)->getQualifiedTest();
        $qualified_tst = ilObjectFactory::getInstanceByRefId($qualified, false);
        if ($qualified_tst instanceof ilObjTest) {
            $this->resetTestForUser($qualified_tst, $this->getUserId());
        }

        $objectives = ilCourseObjective::_getObjectiveIds($a_course_id, false);

        if ($objectives !== []) {
            $query = "DELETE FROM crs_objective_status " .
                "WHERE " . $this->db->in('objective_id', $objectives, false, 'integer') . ' ' .
                "AND user_id = " . $this->db->quote($this->getUserId(), 'integer') . " ";
            $res = $this->db->manipulate($query);

            $query = "DELETE FROM crs_objective_status_p " .
                "WHERE " . $this->db->in('objective_id', $objectives, false, 'integer') . ' ' .
                "AND user_id = " . $this->db->quote($this->getUserId(), ilDBConstants::T_INTEGER) . "";
            $res = $this->db->manipulate($query);

            $query = "DELETE FROM loc_user_results " .
                "WHERE " . $this->db->in('objective_id', $objectives, false, 'integer') . ' ' .
                "AND user_id = " . $this->db->quote($this->getUserId(), ilDBConstants::T_INTEGER) . "";
        }
        // update/reset LP for course
        ilLPStatusWrapper::_updateStatus($a_course_id, $this->getUserId());
    }

    public function getStatus(int $a_course_id) : string
    {
        $objective_ids = ilCourseObjective::_getObjectiveIds($a_course_id, true);
        $objectives = ilCourseObjectiveResult::_readAssignedObjectives($objective_ids);
        $accomplished = $this->getAccomplished($a_course_id);
        $suggested = $this->getSuggested($a_course_id);

        if ($objective_ids === []) {
            return self::IL_OBJECTIVE_STATUS_EMPTY;
        }

        if (count($accomplished) == count($objective_ids)) {
            return self::IL_OBJECTIVE_STATUS_FINISHED;
        }

        $all_pretest_answered = false;
        $all_final_answered = false;
        foreach ($objectives as $data) {
            if (assQuestion::_areAnswered($this->getUserId(), $data['questions'])) {
                if ($data['tst_status']) {
                    $all_final_answered = true;
                } else {
                    $all_pretest_answered = true;
                }
            }
        }
        if ($all_final_answered) {
            return self::IL_OBJECTIVE_STATUS_FINAL;
        }
        if ($all_pretest_answered && $suggested === []) {
            return self::IL_OBJECTIVE_STATUS_PRETEST_NON_SUGGEST;
        } elseif ($all_pretest_answered) {
            return self::IL_OBJECTIVE_STATUS_PRETEST;
        }
        return self::IL_OBJECTIVE_STATUS_NONE;
    }

    public function hasAccomplishedObjective(int $a_objective_id) : bool
    {
        $query = "SELECT status FROM crs_objective_status " .
            "WHERE objective_id = " . $this->db->quote($a_objective_id, 'integer') . " " .
            "AND user_id = " . $this->db->quote($this->getUserId(), 'integer') . "";

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return true;
        }
        return false;
    }

    public function readStatus(int $a_crs_id) : void
    {
        $objective_ids = ilCourseObjective::_getObjectiveIds($a_crs_id, true);
        $objectives = ilCourseObjectiveResult::_readAssignedObjectives($objective_ids);
        ilCourseObjectiveResult::_updateObjectiveStatus($this->getUserId(), $objectives);
    }

    public static function _updateObjectiveResult(int $a_user_id, int $a_active_id, int $a_question_id) : void
    {
        // find all objectives this question is assigned to
        if (!$objectives = self::_readAssignedObjectivesOfQuestion($a_question_id)) {
            // no objectives found. TODO user has passed a test. After that questions of that test are assigned to an objective.
            // => User has not passed
            return;
        }
        self::_updateObjectiveStatus($a_user_id, $objectives);
    }

    public static function _readAssignedObjectivesOfQuestion(int $a_question_id) : array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        // get all objtives and questions this current question is assigned to
        $query = "SELECT q2.question_id qid,q2.objective_id ob FROM crs_objective_qst q1, " .
            "crs_objective_qst q2 " .
            "WHERE q1.question_id = " . $ilDB->quote($a_question_id, 'integer') . " " .
            "AND q1.objective_id = q2.objective_id ";

        $res = $ilDB->query($query);
        $objectives = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $objectives['all_objectives'][(int) $row->ob] = (int) $row->ob;
            $objectives['all_questions'][(int) $row->qid] = (int) $row->qid;
        }
        if (count($objectives) === 0) {
            return [];
        }
        $objectives['objectives'] = self::_readAssignedObjectives($objectives['all_objectives']);
        return $objectives;
    }

    public static function _readAssignedObjectives(array $a_all_objectives) : array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        // Read necessary points
        $query = "SELECT t.objective_id obj,t.ref_id ref, question_id,tst_status,tst_limit " .
            "FROM crs_objective_tst t JOIN crs_objective_qst q " .
            "ON (t.objective_id = q.objective_id AND t.ref_id = q.ref_id) " .
            "WHERE " . $ilDB->in('t.objective_id', $a_all_objectives, false, 'integer');

        $res = $ilDB->query($query);
        $objectives = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $objectives[$row->obj . "_" . $row->tst_status]['questions'][(int) $row->question_id] = (int) $row->question_id;
            $objectives[$row->obj . "_" . $row->tst_status]['tst_status'] = (int) $row->tst_status;
            $objectives[$row->obj . "_" . $row->tst_status]['tst_limit'] = (int) $row->tst_limit;
            $objectives[$row->obj . "_" . $row->tst_status]['objective_id'] = (int) $row->obj;
        }
        return $objectives;
    }

    public static function _updateObjectiveStatus(int $a_user_id, array $objectives) : bool
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];

        if (
            !count($objectives['all_questions']) ||
            !count($objectives['all_objectives'])) {
            return false;
        }
        // Read reachable points
        $query = "SELECT question_id,points FROM qpl_questions " .
            "WHERE " . $ilDB->in('question_id', (array) $objectives['all_questions'], false, 'integer');
        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($res)) {
            $objectives['all_question_points'][(int) $row['question_id']]['max_points'] = (int) $row['points'];
        }
        // Read reached points
        $query = "SELECT question_fi, MAX(points) as reached FROM tst_test_result " .
            "JOIN tst_active ON (active_id = active_fi) " .
            "WHERE user_fi = " . $ilDB->quote($a_user_id, 'integer') . " " .
            "AND " . $ilDB->in('question_fi', (array) $objectives['all_questions'], false, 'integer') . " " .
            "GROUP BY question_fi,user_fi";
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $objectives['all_question_points'][$row->question_fi]['reached_points'] = (int) $row->reached;
        }

        // Check accomplished
        $fullfilled = array();
        $pretest = array();
        foreach ($objectives['objectives'] as $data) {
            // objective does not allow to change status
            if (ilCourseObjectiveResult::__isFullfilled($objectives['all_question_points'], $data)) {
                // Status 0 means pretest fullfilled, status 1 means final test fullfilled
                if ($data['tst_status']) {
                    $fullfilled[] = array($data['objective_id'], $ilUser->getId(), $data['tst_status']);
                } else {
                    $pretest[] = array($data['objective_id'], $ilUser->getId());
                }
            }
        }
        if ($fullfilled !== []) {
            foreach ($fullfilled as $fullfilled_arr) {
                $ilDB->replace(
                    'crs_objective_status',
                    array(
                        'objective_id' => array('integer', $fullfilled_arr[0]),
                        'user_id' => array('integer', $fullfilled_arr[1])
                    ),
                    array(
                        'status' => array('integer', $fullfilled_arr[2])
                    )
                );
            }
            ilCourseObjectiveResult::__updatePassed($a_user_id, $objectives['all_objectives']);
        }
        if ($pretest !== []) {
            foreach ($pretest as $pretest_arr) {
                $ilDB->replace(
                    'crs_objective_status_p',
                    array(
                        'objective_id' => array('integer', $pretest_arr[0]),
                        'user_id' => array('integer', $pretest_arr[1])
                    ),
                    array()
                );
            }
        }
        return true;
    }

    public static function __isFullfilled(array $question_points, array $objective_data) : bool
    {
        if (!is_array($objective_data['questions'])) {
            return false;
        }
        $max_points = 0;
        $reached_points = 0;
        foreach ($objective_data['questions'] as $question_id) {
            $max_points += $question_points[$question_id]['max_points'];
            $reached_points += $question_points[$question_id]['reached_points'];
        }
        if (!$max_points) {
            return false;
        }
        return $reached_points >= $objective_data['tst_limit'];
    }

    protected static function __updatePassed(int $a_user_id, array $objective_ids) : void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $passed = array();

        $query = "SELECT COUNT(t1.crs_id) num,t1.crs_id FROM crs_objectives t1 " .
            "JOIN crs_objectives t2 WHERE t1.crs_id = t2.crs_id and  " .
            $ilDB->in('t1.objective_id', $objective_ids, false, 'integer') . " " .
            "GROUP BY t1.crs_id";
        $res = $ilDB->query($query);
        $crs_ids = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $query = "SELECT COUNT(cs.objective_id) num_passed FROM crs_objective_status cs " .
                "JOIN crs_objectives co ON cs.objective_id = co.objective_id " .
                "WHERE crs_id = " . $ilDB->quote($row->crs_id, 'integer') . " " .
                "AND user_id = " . $ilDB->quote($a_user_id, 'integer') . " ";

            $user_res = $ilDB->query($query);
            while ($user_row = $user_res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                if ((int) $user_row->num_passed === (int) $row->num) {
                    $passed[] = $row->crs_id;
                }
            }
            $crs_ids[(int) $row->crs_id] = (int) $row->crs_id;
        }
        if ($passed !== []) {
            foreach ($passed as $crs_id) {
                $members = ilCourseParticipants::_getInstanceByObjId($crs_id);
                $members->updatePassed($a_user_id, true);
            }
        }

        // update tracking status
        foreach ($crs_ids as $cid) {
            ilLPStatusWrapper::_updateStatus($cid, $a_user_id);
        }
    }
}
