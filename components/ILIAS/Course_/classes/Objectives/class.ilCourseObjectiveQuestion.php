<?php

declare(strict_types=0);
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
 * class ilcourseobjectiveQuestion
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilCourseObjectiveQuestion
{
    public const TYPE_SELF_ASSESSMENT = 0;
    public const TYPE_FINAL_TEST = 1;

    private int $objective_id = 0;
    private array $questions = [];
    private array $tests = [];
    private int $tst_status = 0;
    private int $tst_limit = 0;
    private int $tst_ref_id = 0;
    private int $tst_obj_id = 0;
    private int $question_id = 0;

    protected ilLogger $logger;
    protected ilDBInterface $db;
    protected ilObjectDataCache $objectDataCache;
    protected ilTree $tree;

    public function __construct(int $a_objective_id = 0)
    {
        global $DIC;

        $this->logger = $DIC->logger()->crs();
        $this->objectDataCache = $DIC['ilObjDataCache'];
        $this->db = $DIC->database();
        $this->tree = $DIC->repositoryTree();

        $this->objective_id = $a_objective_id;
        $this->__read();
    }

    /**
     * @return int[]
     */
    public static function lookupObjectivesOfQuestion(int $a_qid): array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = 'SELECT objective_id FROM crs_objective_qst ' .
            'WHERE question_id = ' . $ilDB->quote($a_qid, 'integer');
        $res = $ilDB->query($query);
        $objectiveIds = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $objectiveIds[] = (int) $row->objective_id;
        }
        return $objectiveIds;
    }

    public static function _isTestAssignedToObjective(int $a_test_id, int $a_objective_id): bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT qst_ass_id FROM crs_objective_qst " .
            "WHERE ref_id = " . $ilDB->quote($a_test_id, 'integer') . " " .
            "AND objective_id = " . $ilDB->quote($a_objective_id, 'integer');
        $res = $ilDB->query($query);
        return (bool) $res->numRows();
    }

    public function cloneDependencies(int $a_new_objective, int $a_copy_id): void
    {
        $cwo = ilCopyWizardOptions::_getInstance($a_copy_id);
        $mappings = $cwo->getMappings();
        foreach ($this->getQuestions() as $question) {
            $mapping_key = $question['ref_id'] . '_question_' . $question['question_id'];
            if (!isset($mappings[$mapping_key]) || !$mappings[$mapping_key]) {
                continue;
            }
            $question_ref_id = $question['ref_id'];
            $question_obj_id = $question['obj_id'];
            $question_qst_id = $question['question_id'];
            $new_ref_id = (int) $mappings[$question_ref_id];
            $new_obj_id = $this->objectDataCache->lookupObjId($new_ref_id);

            if ($new_obj_id == $question_obj_id) {
                $this->logger->info('Test has been linked. Keeping question id');
                // Object has been linked
                $new_question_id = $question_qst_id;
            } else {
                $new_question_info = $mappings[$question_ref_id . '_question_' . $question_qst_id];
                $new_question_arr = explode('_', $new_question_info);
                if (!isset($new_question_arr[2]) || !$new_question_arr[2]) {
                    $this->logger->debug('found invalid format of question id mapping: ' . print_r(
                        $new_question_arr,
                        true
                    ));
                    continue;
                }
                $new_question_id = $new_question_arr[2];
                $this->logger->info('New question id is: ' . $new_question_id);
            }

            ilLoggerFactory::getLogger('crs')->debug('Copying question assignments');
            $new_question = new ilCourseObjectiveQuestion($a_new_objective);
            $new_question->setTestRefId($new_ref_id);
            $new_question->setTestObjId($new_obj_id);
            $new_question->setQuestionId($new_question_id);
            $new_question->add();
        }

        // Copy tests
        foreach ($this->getTests() as $test) {
            $new_test_id = $mappings["$test[ref_id]"];

            $query = "UPDATE crs_objective_tst " .
                "SET tst_status = " . $this->db->quote($test['tst_status'], 'integer') . ", " .
                "tst_limit_p = " . $this->db->quote($test['tst_limit'], 'integer') . " " .
                "WHERE objective_id = " . $this->db->quote($a_new_objective, 'integer') . " " .
                "AND ref_id = " . $this->db->quote($new_test_id, 'integer');
            $res = $this->db->manipulate($query);
        }
    }

    public static function _getAssignableTests(int $a_container_ref_id): array
    {
        global $DIC;

        $tree = $DIC->repositoryTree();
        return $tree->getSubTree($tree->getNodeData($a_container_ref_id), true, ['tst']);
    }

    // ########################################################  Methods for test table
    public function setTestStatus(int $a_status): void
    {
        $this->tst_status = $a_status;
    }

    public function getTestStatus(): int
    {
        return $this->tst_status;
    }

    public function setTestSuggestedLimit(int $a_limit): void
    {
        $this->tst_limit = $a_limit;
    }

    public function getTestSuggestedLimit(): int
    {
        return $this->tst_limit;
    }

    public function __addTest(): void
    {
        $query = "UPDATE crs_objective_tst " .
            "SET tst_status = " . $this->db->quote($this->getTestStatus(), 'integer') . " " .
            "WHERE objective_id = " . $this->db->quote($this->getObjectiveId(), 'integer') . " " .
            "AND ref_id = " . $this->db->quote($this->getTestRefId(), 'integer') . " ";
        $res = $this->db->manipulate($query);

        // CHECK if entry already exists
        $query = "SELECT * FROM crs_objective_tst " .
            "WHERE objective_id = " . $this->db->quote($this->getObjectiveId(), 'integer') . " " .
            "AND ref_id = " . $this->db->quote($this->getTestRefId(), 'integer') . "";

        $res = $this->db->query($query);
        if ($res->numRows()) {
            return;
        }

        // Check for existing limit
        $query = "SELECT tst_limit_p FROM crs_objective_tst " .
            "WHERE objective_id = " . $this->db->quote($this->getObjectiveId(), 'integer') . " " .
            "AND tst_status = " . $this->db->quote($this->getTestStatus(), 'integer') . " ";

        $res = $this->db->query($query);

        $limit = 100;
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $limit = (int) $row->tst_limit_p;
        }

        $next_id = $this->db->nextId('crs_objective_tst');
        $query = "INSERT INTO crs_objective_tst (test_objective_id,objective_id,ref_id,obj_id,tst_status,tst_limit_p) " .
            "VALUES( " .
            $this->db->quote($next_id, 'integer') . ", " .
            $this->db->quote($this->getObjectiveId(), 'integer') . ", " .
            $this->db->quote($this->getTestRefId(), 'integer') . ", " .
            $this->db->quote($this->getTestObjId(), 'integer') . ", " .
            $this->db->quote($this->getTestStatus(), 'integer') . ", " .
            $this->db->quote($limit, 'integer') . " " .
            ")";
        $res = $this->db->manipulate($query);
    }

    public function __deleteTest(int $a_test_ref_id): void
    {
        // Delete questions
        $query = "DELETE FROM crs_objective_qst " .
            "WHERE objective_id = " . $this->db->quote($this->getObjectiveId(), 'integer') . " " .
            "AND ref_id = " . $this->db->quote($a_test_ref_id, 'integer') . " ";
        $res = $this->db->manipulate($query);

        // delete tst entries
        $query = "DELETE FROM crs_objective_tst " .
            "WHERE objective_id = " . $this->db->quote($this->getObjectiveId(), 'integer') . " " .
            "AND ref_id = " . $this->db->quote($a_test_ref_id, 'integer') . " ";
        $res = $this->db->manipulate($query);
        unset($this->tests[$a_test_ref_id]);
    }

    public static function _updateTestLimits(int $a_objective_id, int $a_status, int $a_limit): void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "UPDATE crs_objective_tst " .
            "SET tst_limit_p = " . $ilDB->quote($a_limit, 'integer') . " " .
            "WHERE tst_status = " . $ilDB->quote($a_status, 'integer') . " " .
            "AND objective_id = " . $ilDB->quote($a_objective_id, 'integer');
        $res = $ilDB->manipulate($query);
    }

    public function updateTest(int $a_objective_id): void
    {
        $query = "UPDATE crs_objective_tst " .
            "SET tst_status = " . $this->db->quote($this->getTestStatus(), 'integer') . ", " .
            "tst_limit_p = " . $this->db->quote($this->getTestSuggestedLimit(), 'integer') . " " .
            "WHERE test_objective_id = " . $this->db->quote($a_objective_id, 'integer') . "";
        $res = $this->db->manipulate($query);
    }

    public function getTests(): array
    {
        $query = "SELECT * FROM crs_objective_tst cot " .
            "JOIN object_data obd ON cot.obj_id = obd.obj_id " .
            "WHERE objective_id = " . $this->db->quote($this->getObjectiveId(), 'integer') . " " .
            "ORDER BY title ";

        $res = $this->db->query($query);
        $tests = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $test['test_objective_id'] = (int) $row->test_objective_id;
            $test['objective_id'] = (int) $row->objective_id;
            $test['ref_id'] = (int) $row->ref_id;
            $test['obj_id'] = (int) $row->obj_id;
            $test['tst_status'] = (int) $row->tst_status;
            $test['tst_limit'] = (int) $row->tst_limit_p;
            $test['title'] = (string) $row->title;

            $tests[] = $test;
        }
        return $tests;
    }

    public function getSelfAssessmentTests(): array
    {
        $self = [];
        foreach ($this->tests as $test) {
            if ($test['status'] == self::TYPE_SELF_ASSESSMENT) {
                $self[] = $test;
            }
        }
        return $self;
    }

    public function getFinalTests(): array
    {
        $final = [];
        foreach ($this->tests as $test) {
            if ($test['status'] == self::TYPE_FINAL_TEST) {
                $final[] = $test;
            }
        }
        return $final;
    }

    public static function _getTest(int $a_test_objective_id): array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "SELECT * FROM crs_objective_tst " .
            "WHERE test_objective_id = " . $ilDB->quote($a_test_objective_id, 'integer') . " ";

        $res = $ilDB->query($query);
        $test = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $test['test_objective_id'] = (int) $row->test_objective_id;
            $test['objective_id'] = (int) $row->objective_id;
            $test['ref_id'] = (int) $row->ref_id;
            $test['obj_id'] = (int) $row->obj_id;
            $test['tst_status'] = (int) $row->tst_status;
            $test['tst_limit'] = (int) $row->tst_limit_p;
        }

        return $test;
    }

    public function getQuestions(): array
    {
        return $this->questions;
    }

    public function getSelfAssessmentQuestions(): array
    {
        $self = [];
        foreach ($this->questions as $question) {
            if ($question['test_type'] == self::TYPE_SELF_ASSESSMENT) {
                $self[] = $question;
            }
        }
        return $self;
    }

    public function getSelfAssessmentPoints(): int
    {
        $points = 0;
        foreach ($this->getSelfAssessmentQuestions() as $question) {
            $points += $question['points'];
        }
        return $points;
    }

    public function getFinalTestPoints(): int
    {
        $points = 0;
        foreach ($this->getFinalTestQuestions() as $question) {
            $points += $question['points'];
        }
        return $points;
    }

    public function isSelfAssessmentQuestion(int $a_question_id): bool
    {
        foreach ($this->questions as $question) {
            if ($question['question_id'] == $a_question_id) {
                return $question['test_type'] === self::TYPE_SELF_ASSESSMENT;
            }
        }
        return false;
    }

    public function isFinalTestQuestion(int $a_question_id): bool
    {
        foreach ($this->questions as $question) {
            if ($question['question_id'] == $a_question_id) {
                return $question['test_type'] === self::TYPE_FINAL_TEST;
            }
        }
        return false;
    }

    public function getFinalTestQuestions(): array
    {
        $final = [];
        foreach ($this->questions as $question) {
            if ($question['test_type'] == self::TYPE_FINAL_TEST) {
                $final[] = $question;
            }
        }
        return $final;
    }

    public function getQuestionsOfTest(int $a_test_id): array
    {
        $questions = [];
        foreach ($this->getQuestions() as $qst) {
            if ($a_test_id == $qst['obj_id']) {
                $questions[] = $qst;
            }
        }
        return $questions;
    }

    public function getQuestion(int $question_id): array
    {
        if ($this->questions[$question_id]) {
            return $this->questions[$question_id];
        } else {
            return array();
        }
    }

    public function getObjectiveId(): int
    {
        return $this->objective_id;
    }

    public function setTestRefId(int $a_ref_id): void
    {
        $this->tst_ref_id = $a_ref_id;
    }

    public function getTestRefId(): int
    {
        return $this->tst_ref_id;
    }

    public function setTestObjId(int $a_obj_id): void
    {
        $this->tst_obj_id = $a_obj_id;
    }

    public function getTestObjId(): int
    {
        return $this->tst_obj_id;
    }

    public function setQuestionId(int $a_question_id): void
    {
        $this->question_id = $a_question_id;
    }

    public function getQuestionId(): int
    {
        return $this->question_id;
    }

    public function getMaxPointsByObjective(): int
    {
        $points = 0;
        foreach ($this->getQuestions() as $question) {
            $tmp_test = ilObjectFactory::getInstanceByRefId($question['ref_id']);
            $tmp_question = ilObjTest::_instanciateQuestion($question['question_id']);
            $points += $tmp_question->getMaximumPoints();
        }
        return $points;
    }

    public function getMaxPointsByTest(int $a_test_ref_id): int
    {
        $points = 0;
        $tmp_test = ilObjectFactory::getInstanceByRefId($a_test_ref_id);
        foreach ($this->getQuestions() as $question) {
            if ($question['ref_id'] == $a_test_ref_id) {
                $tmp_question = ilObjTest::_instanciateQuestion($question['question_id']);
                $points += $tmp_question->getMaximumPoints();
            }
        }
        return $points;
    }

    public static function _lookupMaximumPointsOfQuestion(int $a_question_id): float
    {
        return assQuestion::_getMaximumPoints($a_question_id);
    }

    public function getNumberOfQuestionsByTest(int $a_test_ref_id): int
    {
        $counter = 0;
        foreach ($this->getQuestions() as $question) {
            if ($question['ref_id'] == $a_test_ref_id) {
                ++$counter;
            }
        }
        return $counter;
    }

    public function getQuestionsByTest(int $a_test_ref_id): array
    {
        $qst = [];
        foreach ($this->getQuestions() as $question) {
            if ($question['ref_id'] == $a_test_ref_id) {
                $qst[] = $question['question_id'];
            }
        }
        return $qst;
    }

    public function updateLimits(): void
    {
        $points = 0;
        foreach ($this->tests as $test_data) {
            switch ($test_data['status']) {
                case self::TYPE_SELF_ASSESSMENT:
                    $points = $this->getSelfAssessmentPoints();
                    break;

                case self::TYPE_FINAL_TEST:
                    $points = $this->getFinalTestPoints();
                    break;
            }
            if ($test_data['limit'] == -1 || $test_data['limit'] > $points) {
                switch ($test_data['status']) {
                    case self::TYPE_SELF_ASSESSMENT:
                        $points = $this->getSelfAssessmentPoints();
                        break;

                    case self::TYPE_FINAL_TEST:
                        $points = $this->getFinalTestPoints();
                        break;
                }
                $query = "UPDATE crs_objective_tst " .
                    "SET tst_limit = " . $this->db->quote($points, 'integer') . " " .
                    "WHERE test_objective_id = " . $this->db->quote($test_data['test_objective_id'], 'integer') . " ";
                $res = $this->db->manipulate($query);
            }
        }
    }

    public function add(): void
    {
        $query = "DELETE FROM crs_objective_qst " .
            "WHERE objective_id = " . $this->db->quote($this->getObjectiveId(), 'integer') . " " .
            "AND question_id = " . $this->db->quote($this->getQuestionId(), 'integer') . " ";
        $res = $this->db->manipulate($query);

        $next_id = $this->db->nextId('crs_objective_qst');
        $query = "INSERT INTO crs_objective_qst (qst_ass_id, objective_id,ref_id,obj_id,question_id) " .
            "VALUES( " .
            $this->db->quote($next_id, 'integer') . ", " .
            $this->db->quote($this->getObjectiveId(), 'integer') . ", " .
            $this->db->quote($this->getTestRefId(), 'integer') . ", " .
            $this->db->quote($this->getTestObjId(), 'integer') . ", " .
            $this->db->quote($this->getQuestionId(), 'integer') .
            ")";
        $res = $this->db->manipulate($query);

        $this->__addTest();
        $this->__read();
    }

    public function delete(int $qst_id): void
    {
        if (!$qst_id) {
            return;
        }

        $query = "SELECT * FROM crs_objective_qst " .
            "WHERE qst_ass_id = " . $this->db->quote($qst_id, 'integer') . " ";

        $test_rid = $test_oid = 0;
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $test_rid = (int) $row->ref_id;
            $test_oid = (int) $row->obj_id;
        }

        $query = "DELETE FROM crs_objective_qst " .
            "WHERE qst_ass_id = " . $this->db->quote($qst_id, 'integer') . " ";
        $res = $this->db->manipulate($query);

        // delete test if it was the last question
        $query = "SELECT * FROM crs_objective_qst " .
            "WHERE ref_id = " . $this->db->quote($test_rid, 'integer') . " " .
            "AND obj_id = " . $this->db->quote($test_oid, 'integer') . " " .
            "AND objective_id = " . $this->db->quote($this->getObjectiveId(), 'integer') . " ";

        $res = $this->db->query($query);
        if ($res->numRows() === 0) {
            $this->__deleteTest($test_rid);
        }
    }

    // begin-patch lok
    public static function deleteTest(int $a_tst_ref_id): void
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = 'DELETE FROM crs_objective_tst ' .
            'WHERE ref_id = ' . $ilDB->quote($a_tst_ref_id, 'integer');
        $ilDB->manipulate($query);

        $query = 'DELETE FROM crs_objective_qst ' .
            'WHERE ref_id = ' . $ilDB->quote($a_tst_ref_id, 'integer');
        $ilDB->manipulate($query);
    }

    public function deleteByTestType(int $a_type): void
    {
        // Read tests by type
        $deletable_refs = array();
        foreach ($this->tests as $tst_data) {
            if ($tst_data['status'] == $a_type) {
                $deletable_refs[] = $tst_data['ref_id'];
            }
        }

        $query = 'DELETE from crs_objective_tst ' .
            'WHERE objective_id = ' . $this->db->quote($this->getObjectiveId(), 'integer') . ' ' .
            'AND tst_status = ' . $this->db->quote($a_type, 'integer');
        $this->db->manipulate($query);

        $query = 'DELETE from crs_objective_tst ' .
            'WHERE objective_id = ' . $this->db->quote($this->getObjectiveId(), 'integer') . ' ' .
            'AND ' . $this->db->in('ref_id', $deletable_refs, false, 'integer');
        $this->db->manipulate($query);
    }

    public function deleteAll(): void
    {
        $query = "DELETE FROM crs_objective_qst " .
            "WHERE objective_id = " . $this->db->quote($this->getObjectiveId(), 'integer') . " ";
        $res = $this->db->manipulate($query);

        $query = "DELETE FROM crs_objective_tst " .
            "WHERE objective_id = " . $this->db->quote($this->getObjectiveId(), 'integer') . " ";
        $res = $this->db->manipulate($query);
    }

    public function __read(): void
    {
        $container_ref_ids = ilObject::_getAllReferences(ilCourseObjective::_lookupContainerIdByObjectiveId($this->objective_id));
        $container_ref_id = current($container_ref_ids);

        // Read test data
        $query = "SELECT * FROM crs_objective_tst " .
            "WHERE objective_id = " . $this->db->quote($this->getObjectiveId(), 'integer') . " ";
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->tests[(int) $row->ref_id]['test_objective_id'] = (int) $row->test_objective_id;
            $this->tests[(int) $row->ref_id]['ref_id'] = (int) $row->ref_id;
            $this->tests[(int) $row->ref_id]['obj_id'] = (int) $row->obj_id;
            $this->tests[(int) $row->ref_id]['status'] = (int) $row->tst_status;
            $this->tests[(int) $row->ref_id]['limit'] = (int) $row->tst_limit_p;
        }

        $this->questions = array();
        $query = "SELECT * FROM crs_objective_qst coq " .
            "JOIN qpl_questions qq ON coq.question_id = qq.question_id " .
            "WHERE objective_id = " . $this->db->quote($this->getObjectiveId(), 'integer') . " " .
            "ORDER BY title";

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if (!$this->tree->isInTree((int) $row->ref_id) || !$this->tree->isGrandChild(
                $container_ref_id,
                (int) $row->ref_id
            )) {
                $this->__deleteTest((int) $row->ref_id);
                continue;
            }
            if (($question = ilObjTest::_instanciateQuestion((int) $row->question_id)) === null) {
                $this->delete((int) $row->question_id);
                continue;
            }
            $qst['ref_id'] = (int) $row->ref_id;
            $qst['obj_id'] = (int) $row->obj_id;
            $qst['question_id'] = (int) $row->question_id;
            $qst['qst_ass_id'] = (int) $row->qst_ass_id;
            $qst['title'] = $question->getTitle();
            $qst['description'] = $question->getComment();
            $qst['test_type'] = (int) $this->tests[(int) $row->ref_id]['status'];
            $qst['points'] = (int) $question->getPoints();

            $this->questions[(int) $row->qst_ass_id] = $qst;
        }
    }

    public static function _hasTests(int $a_course_id): bool
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT co.objective_id FROM crs_objectives co JOIN " .
            "crs_objective_tst cot ON co.objective_id = cot.objective_id " .
            "WHERE crs_id = " . $ilDB->quote($a_course_id, 'integer') . " ";
        $res = $ilDB->query($query);
        return (bool) $res->numRows();
    }

    public static function _isAssigned(int $a_objective_id, int $a_tst_ref_id, int $a_question_id): int
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "SELECT crs_qst.objective_id objective_id FROM crs_objective_qst crs_qst, crs_objectives crs_obj " .
            "WHERE crs_qst.objective_id = crs_obj.objective_id " .
            "AND crs_qst.objective_id = " . $ilDB->quote($a_objective_id, 'integer') . " " .
            "AND ref_id = " . $ilDB->quote($a_tst_ref_id, 'integer') . " " .
            "AND question_id = " . $ilDB->quote($a_question_id, 'integer') . " ";

        $res = $ilDB->query($query);
        $objective_id = 0;
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $objective_id = (int) $row->objective_id;
        }
        return $objective_id;
    }

    public static function lookupQuestionsByObjective(int $a_test_id, int $a_objective): array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = 'SELECT question_id FROM crs_objective_qst ' .
            'WHERE objective_id = ' . $ilDB->quote($a_objective, 'integer') . ' ' .
            'AND obj_id = ' . $ilDB->quote($a_test_id, 'integer');
        $res = $ilDB->query($query);

        $questions = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $questions[] = $row->question_id;
        }
        return $questions;
    }

    public static function loookupTestLimit(int $a_test_id, int $a_objective_id): int
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $query = 'SELECT tst_limit_p FROM crs_objective_tst ' .
            'WHERE objective_id = ' . $ilDB->quote($a_objective_id, 'integer') . ' ' .
            'AND obj_id = ' . $ilDB->quote($a_test_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->tst_limit_p;
        }
        return 0;
    }

    public function toXml(ilXmlWriter $writer): void
    {
        foreach ($this->getTests() as $test) {
            $writer->xmlStartTag(
                'Test',
                array(
                    'type' => ilLOXmlWriter::TYPE_TST_ALL,
                    'refId' => $test['ref_id'],
                    'testType' => $test['tst_status'],
                    'limit' => $test['tst_limit']
                )
            );

            // questions
            foreach ($this->getQuestionsByTest($test['ref_id']) as $question_id) {
                $writer->xmlElement('Question', array('id' => $question_id));
            }
            $writer->xmlEndTag('Test');
        }
    }

    // end-patch lok
}
