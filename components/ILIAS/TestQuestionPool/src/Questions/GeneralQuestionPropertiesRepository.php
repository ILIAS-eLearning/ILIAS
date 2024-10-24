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

namespace ILIAS\TestQuestionPool\Questions;

class GeneralQuestionPropertiesRepository
{
    private const MAIN_QUESTION_TABLE = 'qpl_questions';
    private const QUESTION_TYPES_TABLE = 'qpl_qst_type';
    private const TEST_FIXED_QUESTION_TABLE = 'tst_test_question';
    private const TEST_RANDOM_QUESTION_TABLE = 'tst_test_rnd_qst';
    private const TEST_RESULTS_TABLE = 'tst_test_result';
    private const TEST_TO_ACTIVE_USER_TABLE = 'tst_active';
    private const DATA_TABLE = 'object_data';

    public function __construct(
        private \ilDBInterface $db,
        private \ilComponentFactory $component_factory,
        private \ilComponentRepository $component_repository
    ) {
    }

    public function getForQuestionId(int $question_id): ?GeneralQuestionProperties
    {
        if ($question_id < 1) {
            return new GeneralQuestionProperties($this->component_factory, $question_id);
        }

        $question_data = $this->getForWhereClause('q.question_id=' . $question_id);
        return $question_data[$question_id] ?? null;
    }

    /**
     * @return array<GeneralQuestionProperties>
     */
    public function getForParentObjectId(int $obj_id): array
    {
        return $this->getForWhereClause('q.obj_fi=' . $obj_id);
    }

    /**
     *
     * @param array<int> $question_ids
     * @return array<GeneralQuestionProperties>
     */
    public function getForQuestionIds(array $question_ids): array
    {
        return $this->getForWhereClause(
            $this->db->in(
                'q.question_id',
                $question_ids,
                false,
                \ilDBConstants::T_INTEGER
            )
        );
    }

    public function getFractionOfReachedToReachablePointsTotal(int $question_id): float
    {
        $questions_result = $this->db->queryF(
            'SELECT question_id, points FROM ' . self::MAIN_QUESTION_TABLE . ' WHERE original_id = %s OR question_id = %s',
            [\ilDBConstants::T_INTEGER,\ilDBConstants::T_INTEGER],
            [$question_id, $question_id]
        );
        if ($this->db->numRows($questions_result) === 0) {
            return 0.0;
        }

        $found_questions = [];
        while ($found_questions_row = $this->db->fetchObject($questions_result)) {
            $found_questions[$found_questions_row->question_id] = $found_questions_row->points;
        }

        $points_result = $this->db->query(
            'SELECT question_fi, points FROM ' . self::TEST_RESULTS_TABLE
            . ' WHERE ' . $this->db->in('question_fi', array_keys($found_questions), false, \ilDBConstants::T_INTEGER)
        );

        $answers = [];
        while ($points_row = $this->db->fetchObject($points_result)) {
            $answers[] = [
                'reached' => $points_row->points,
                'reachable' => $found_questions[$points_row->question_fi]
            ];
        }

        $reachable = 0.0;
        $reached = 0.0;
        foreach ($answers as $points) {
            $reachable += $points['reachable'];
            $reached += $points['reached'];
        }
        if ($reachable > 0) {
            return $reached / $reachable;
        }
        return 0;
    }

    /**
     * Checks if an array of question ids is answered by a user or not
     *
     * @param int user_id
     * @param array<int> $question_ids
     */
    public function areQuestionsAnsweredByUser(int $user_id, array $question_ids): bool
    {
        $result = $this->db->queryF(
            'SELECT COUNT(DISTINCT question_fi) cnt FROM ' . self::TEST_RESULTS_TABLE . ' JOIN tst_active'
            . ' ON (active_id = active_fi)'
            . ' WHERE ' . $this->db->in('question_fi', $question_ids, false, \ilDBConstants::T_INTEGER)
            . ' AND user_fi = %s',
            [\ilDBConstants::T_INTEGER],
            [$user_id]
        );

        $row = $this->db->fetchObject($result);
        return $row->cnt === count($question_ids);
    }

    public function lookupResultRecordExist(int $active_id, int $question_id, int $pass): bool
    {
        $result = $this->db->queryF(
            'SELECT COUNT(*) cnt '
            . ' FROM ' . self::TEST_RESULTS_TABLE
            . ' WHERE active_fi = %s'
            . ' AND question_fi = %s'
            . ' AND pass = %s',
            [\ilDBConstants::T_INTEGER, \ilDBConstants::T_INTEGER, \ilDBConstants::T_INTEGER],
            [$active_id, $question_id, $pass]
        );

        $row = $this->db->fetchObject($result);
        return $row->cnt > 0;
    }

    public function isInUse(int $question_id = 0): bool
    {
        return $this->usageCount($question_id) > 0;
    }

    /**
     * Returns the number of place the question is in use in pools or tests
     */
    public function usageCount(int $question_id = 0): int
    {
        $result_tests_fixed = $this->db->queryF(
            'SELECT COUNT(' . self::MAIN_QUESTION_TABLE . '.question_id) question_count'
            . ' FROM ' . self::MAIN_QUESTION_TABLE . ', ' . self::TEST_FIXED_QUESTION_TABLE
            . ' WHERE ' . self::MAIN_QUESTION_TABLE . '.question_id = ' . self::TEST_FIXED_QUESTION_TABLE . '.question_fi'
            . ' AND ' . self::MAIN_QUESTION_TABLE . '.original_id = %s',
            [\ilDBConstants::T_INTEGER],
            [$question_id]
        );
        $row_tests_fixed = $this->db->fetchObject($result_tests_fixed);
        $count = $row_tests_fixed->question_count;

        $result_tests_random = $this->db->queryF(
            'SELECT COUNT(' . self::TEST_TO_ACTIVE_USER_TABLE . '.test_fi) question_count'
            . ' FROM ' . self::MAIN_QUESTION_TABLE
            . ' INNER JOIN ' . self::TEST_RANDOM_QUESTION_TABLE
            . ' ON ' . self::TEST_RANDOM_QUESTION_TABLE . '.question_fi = ' . self::MAIN_QUESTION_TABLE . '.question_id'
            . ' INNER JOIN ' . self::TEST_TO_ACTIVE_USER_TABLE
            . ' ON ' . self::TEST_TO_ACTIVE_USER_TABLE . '.active_id = ' . self::TEST_RANDOM_QUESTION_TABLE . '.active_fi'
            . ' WHERE ' . self::MAIN_QUESTION_TABLE . '.original_id = %s'
            . ' GROUP BY tst_active.test_fi',
            [\ilDBConstants::T_INTEGER],
            [$question_id]
        );
        $row_tests_random = $this->db->fetchObject($result_tests_random);
        if ($row_tests_random !== null) {
            $count += $row_tests_random->question_count;
        }

        return $count;
    }

    /**
     * @return array<int>
     */
    public function searchQuestionIdsByTitle(string $title): array
    {
        if ($title === '') {
            return [];
        }

        $result = $this->db->query(
            'SELECT question_id  FROM ' . self::MAIN_QUESTION_TABLE . ' WHERE '
                . $this->db->like('title', \ilDBConstants::T_TEXT, "%{$title}%"),
        );

        return array_map(
            static fn(\stdClass $q): int => $q->question_id,
            $this->db->fetchAll($result, \ilDBConstants::FETCHMODE_OBJECT)
        );
    }

    public function questionExists(int $question_id): bool
    {
        if ($question_id < 1) {
            return false;
        }

        $result = $this->db->queryF(
            'SELECT COUNT(question_id) cnt FROM ' . self::MAIN_QUESTION_TABLE . ' WHERE question_id = %s',
            [\ilDBConstants::T_INTEGER],
            [$question_id]
        );

        $row = $this->db->fetchObject($result);
        return $row->cnt === 1;
    }

    public function questionExistsInPool(int $question_id): bool
    {
        if ($question_id < 1) {
            return false;
        }

        $result = $this->db->queryF(
            'SELECT COUNT(question_id) cnt FROM ' . self::MAIN_QUESTION_TABLE
            . ' INNER JOIN ' . self::DATA_TABLE . ' ON obj_fi = obj_id WHERE question_id = %s AND type = "qpl"',
            [\ilDBConstants::T_INTEGER],
            [$question_id]
        );

        $row = $this->db->fetchObject($result);
        return $row->cnt === 1;
    }

    public function isUsedInRandomTest(int $question_id): bool
    {
        $result = $this->db->queryF(
            'SELECT COUNT(test_random_question_id) cnt'
            . ' FROM ' . self::TEST_RANDOM_QUESTION_TABLE
            . ' WHERE question_fi = %s',
            [\ilDBConstants::T_INTEGER],
            [$question_id]
        );

        $row = $this->db->fetchObject($result);
        return $row->cnt > 0;
    }

    public function originalQuestionExists(int $question_id): bool
    {
        $res = $this->db->queryF(
            'SELECT COUNT(dupl.question_id) cnt'
            . ' FROM ' . self::MAIN_QUESTION_TABLE . ' dupl'
            . ' INNER JOIN ' . self::MAIN_QUESTION_TABLE . ' orig'
            . ' ON orig.question_id = dupl.original_id'
            . ' WHERE dupl.question_id = %s',
            [\ilDBConstants::T_INTEGER],
            [$question_id]
        );
        $row = $this->db->fetchObject($res);

        return $row->cnt > 0;
    }

    public function getQuestionsMissingResultRecord(
        int $active_id,
        int $pass,
        array $question_ids
    ): array {
        $in_question_ids = $this->db->in('question_fi', $question_ids, false, \ilDBConstants::T_INTEGER);

        $result = $this->db->queryF(
            'SELECT question_fi'
            . ' FROM ' . self::TEST_RESULTS_TABLE
            . ' WHERE active_fi = %s'
            . ' AND pass = %s'
            . ' AND ' . $in_question_ids,
            [\ilDBConstants::T_INTEGER, \ilDBConstants::T_INTEGER],
            [$active_id, $pass]
        );

        $questions_having_result_record = [];

        while ($row = $this->db->fetchObject($result)) {
            $questions_having_result_record[] = $row->question_fi;
        }

        $questions_missing_result_record = array_diff(
            $question_ids,
            $questions_having_result_record
        );

        return $questions_missing_result_record;
    }

    public function missingResultRecordExists(int $active_id, int $pass, array $question_ids): bool
    {
        $in_question_ids = $this->db->in('question_fi', $question_ids, false, \ilDBConstants::T_INTEGER);

        $result = $this->db->queryF(
            'SELECT COUNT(*) cnt'
            . ' FROM ' . self::TEST_RESULTS_TABLE
            . ' WHERE active_fi = %s'
            . ' AND pass = %s'
            . ' AND ' . $in_question_ids,
            [\ilDBConstants::T_INTEGER, \ilDBConstants::T_INTEGER],
            [$active_id, $pass]
        );

        $row = $this->db->fetchAssoc($result);
        return $row->cnt < count($question_ids);
    }

    public function isInActiveTest(int $obj_id): bool
    {
        $result = $this->db->query(
            'SELECT COUNT(user_fi) cnt FROM ' . self::TEST_TO_ACTIVE_USER_TABLE
            . ' JOIN ' . self::TEST_FIXED_QUESTION_TABLE
            . ' ON ' . self::TEST_FIXED_QUESTION_TABLE . '.test_fi = ' . self::TEST_TO_ACTIVE_USER_TABLE . '.test_fi '
            . ' JOIN ' . self::MAIN_QUESTION_TABLE
            . ' ON ' . self::MAIN_QUESTION_TABLE . '.question_id = ' . self::TEST_FIXED_QUESTION_TABLE . '.question_fi '
            . ' WHERE ' . self::MAIN_QUESTION_TABLE . '.obj_fi = ' . $this->db->quote($obj_id, \ilDBConstants::T_INTEGER)
        );

        $row = $this->db->fetchObject($result);
        return $row->cnt > 0;
    }

    public function questionTitleExistsInPool(int $questionpool_id, string $title): bool
    {
        $result = $this->db->queryF(
            'SELECT COUNT(*) cnt FROM ' . self::MAIN_QUESTION_TABLE
            . ' WHERE obj_fi = %s AND title = %s',
            [\ilDBConstants::T_INTEGER, \ilDBConstants::T_TEXT],
            [$questionpool_id, $title]
        );
        $row = $this->db->fetchObject($result);
        return $row->cnt > 0;
    }

    private function buildGeneralQuestionPropertyFromDBRecords(\stdClass $db_record): GeneralQuestionProperties
    {
        return new GeneralQuestionProperties(
            $this->component_factory,
            $db_record->question_id,
            $db_record->original_id,
            $db_record->external_id,
            $db_record->obj_fi,
            $db_record->oq_obj_fi,
            $db_record->question_type_fi,
            $db_record->type_tag,
            $db_record->owner,
            $db_record->title,
            $db_record->description,
            $db_record->question_text,
            $db_record->points,
            $db_record->nr_of_tries,
            $db_record->lifecycle,
            $db_record->author,
            $db_record->tstamp,
            $db_record->created,
            (bool) $db_record->complete,
            $db_record->add_cont_edit_mode
        );
    }

    /**
     * @return array<GeneralQuestionProperties>
     */
    private function getForWhereClause(string $where): array
    {
        $query_result = $this->db->query(
            'SELECT q.*, qt.type_tag, qt.plugin as is_plugin, qt.plugin_name, oq.obj_fi as oq_obj_fi'
            . ' FROM ' . self::MAIN_QUESTION_TABLE . ' q'
            . ' INNER JOIN ' . self::QUESTION_TYPES_TABLE . ' qt'
            . ' ON q.question_type_fi = qt.question_type_id'
            . ' LEFT JOIN ' . self::MAIN_QUESTION_TABLE . ' oq'
            . ' ON oq.question_id = q.original_id'
            . ' WHERE ' . $where
        );

        $questions = [];
        while ($db_record = $this->db->fetchObject($query_result)) {
            if (!$this->isQuestionTypeAvailable($db_record->plugin_name)) {
                continue;
            }
            $questions[$db_record->question_id] = $this
                ->buildGeneralQuestionPropertyFromDBRecords($db_record);
        }
        return $questions;
    }

    /*
     * $param array<stdClass> $question_data
     */
    private function isQuestionTypeAvailable(?string $plugin_name): bool
    {
        if ($plugin_name === null) {
            return true;
        }

        $plugin_slot = !$this->component_repository->getComponentByTypeAndName(
            ilComponentInfo::TYPE_MODULES,
            'TestQuestionPool'
        )->getPluginSlotById('qst');

        if ($plugin_slot->hasPluginName($plugin_name)) {
            return false;
        }

        return $plugin_slot->getPluginByName($plugin_name)->isActive();
    }
}
