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
    /**
     * @var array<ILIAS\TestQuestionPool\Questions\GeneralQuestionProperties>
     */
    private static array $general_properties_cache = [];

    public function __construct(
        private \ilDBInterface $database,
        private \ilComponentFactory $component_factory,
        private \ilLanguage $lng
    ) {
    }

    public function getForQuestionId(int $question_id): ?GeneralQuestionProperties
    {
        if ($question_id < 1) {
            return new GeneralQuestionProperties($question_id);
        }

        if (!isset(self::$general_properties_cache[$question_id])) {
            self::$general_properties_cache[$question_id] = $this->retrieveGeneralProperties($question_id);
        }

        return self::$general_properties_cache[$question_id];
    }

    private function retrieveGeneralProperties(int $question_id): GeneralQuestionProperties
    {
        $query_result = $this->database->queryF(
            'SELECT q.*, qt.type_tag, rnd.test_random_question_id'
            . ' FROM ' . self::MAIN_QUESTION_TABLE . ' q'
            . ' INNER JOIN ' . self::QUESTION_TYPES_TABLE . ' qt'
            . ' ON qpl_questions.question_type_fi = qpl_qst_type.question_type_id'
            . ' WHERE question_id = %s',
            [\ilDBConstants::T_INTEGER],
            [$question_id]
        );

        if ($this->database->numRows($query_result) === 0) {
            return null;
        }

        $question_info = $this->database->fetchObject($query_result);
        return new GeneralQuestionProperties(
            $this->component_factory,
            $question_id,
            $question_info->original_id,
            $question_info->external_id,
            $question_info->obj_fi,
            $question_info->question_type_fi,
            $question_info->type_tag,
            $question_info->owner,
            $question_info->title,
            $question_info->description,
            $question_info->question_text,
            $question_info->points,
            $question_info->nr_of_tries,
            $question_info->lifecycle,
            $question_info->author,
            $question_info->tstamp,
            $question_info->created,
            $question_info->complete,
            $question_info->add_cont_edit_mode
        );
    }



    public function getFractionOfReachedToReachablePointsTotal(int $question_id): float
    {

        $questions_result = $this->database->queryF(
            'SELECT question_id, points FROM ' . self::MAIN_QUESTION_TABLE . ' WHERE original_id = %s OR question_id = %s',
            ['integer','integer'],
            [$question_id, $question_id]
        );
        if ($this->database->numRows($questions_result) === 0) {
            return 0.0;
        }

        $found_questions = [];
        while ($found_questions_row = $this->database->fetchObject($questions_result)) {
            $found_questions[$found_questions_row->question_id] = $found_questions_row->points;
        }

        $points_result = $this->database->query(
            'SELECT question_fi, points FROM ' . self::TEST_RESULTS_TABLE
            . ' WHERE ' . $this->database->in('question_fi', array_keys($found_questions), false, 'integer')
        );

        $answers = [];
        while ($points_row = $this->database->fetchObject($points_result)) {
            $answers[] = [
                'reached' => $points_row->points,
                'reachable' => $points_result[$points_row->question_fi]
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
     * @param int[] $question_ids user id array
     */
    public function areQuestionsAnsweredByUser(int $user_id, array $question_ids): bool
    {
        $result = $this->database->queryF(
            'SELECT COUNT(DISTINCT question_fi) cnt FROM ' . self::TEST_RESULTS_TABLE . ' JOIN tst_active'
            . ' ON (active_id = active_fi)'
            . ' WHERE ' . $this->database->in('question_fi', $question_ids, false, 'integer')
            . ' AND user_fi = %s',
            ['integer'],
            [$user_id]
        );

        $row = $this->database->fetchObject($result);
        return $row->cnt === count($question_ids);
    }

    public function lookupResultRecordExist(int $active_id, int $question_id, int $pass): bool
    {
        $result = $this->database->queryF(
            'SELECT COUNT(*) cnt '
            . ' FROM ' . self::TEST_RESULTS_TABLE
            . ' WHERE active_fi = %s'
            . ' AND question_fi = %s'
            . ' AND pass = %s',
            ['integer', 'integer', 'integer'],
            [$active_id, $question_id, $pass]
        );

        $row = $this->database->fetchObject($result);
        return $row->cnt > 0;
    }

    /**
     * Checks whether the question is in use or not in pools or tests
     */
    public function isInUse(int $question_id = 0): bool
    {
        return $this->usageCount($question_id) > 0;
    }

    /**
     * Returns the number of place the question is in use in pools or tests
     */
    public function usageCount(int $question_id = 0): int
    {
        $result_tests_fixed = $this->database->queryF(
            'SELECT COUNT(qpl_questions.question_id) question_count'
            . ' FROM ' . self::MAIN_QUESTION_TABLE . ', ' . self::TEST_FIXED_QUESTION_TABLE
            . ' WHERE ' . self::MAIN_QUESTION_TABLE . '.question_id = ' . self::TEST_FIXED_QUESTION_TABLE . '.question_fi'
            . ' AND ' . self::MAIN_QUESTION_TABLE . '.original_id = %s',
            ['integer'],
            [$question_id]
        );
        $row_tests_fixed = $this->database->fetchObject($result_tests_fixed);
        $count = $row_tests_fixed->question_count;

        $result_tests_random = $this->database->queryF(
            'SELECT COUNT(' . self::TEST_RANDOM_QUESTION_TABLE . '.test_fi) question_count'
            . ' FROM qpl_questions'
            . ' INNER JOIN ' . self::TEST_RANDOM_QUESTION_TABLE
            . ' ON ' . self::TEST_RANDOM_QUESTION_TABLE . '.question_fi = ' . self::MAIN_QUESTION_TABLE . '.question_id'
            . ' INNER JOIN ' . self::TEST_TO_ACTIVE_USER_TABLE . ' ON ' . self::TEST_TO_ACTIVE_USER_TABLE . '.active_id = ' . self::TEST_RANDOM_QUESTION_TABLE . '.active_fi'
            . ' WHERE ' . self::MAIN_QUESTION_TABLE . '.original_id = %s'
            . ' GROUP BY tst_active.test_fi',
            ['integer'],
            [$question_id]
        );
        $row_tests_random = $this->database->fetchObject($result_tests_random);
        $count += $$row_tests_random->question_count;

        return $count;
    }

    public function questionExists(int $question_id): bool
    {
        if ($question_id < 1) {
            return false;
        }

        $result = $this->database->queryF(
            'SELECT COUNT(question_id) cnt FROM ' . self::MAIN_QUESTION_TABLE . ' WHERE question_id = %s',
            ['integer'],
            [$question_id]
        );

        $row = $this->database->fetchObject($result);
        return $row->cnt === 1;
    }

    public function questionExistsInPool(int $question_id): bool
    {
        if ($question_id < 1) {
            return false;
        }

        $result = $this->database->queryF(
            'SELECT COUNT(question_id) cnt FROM ' . self::MAIN_QUESTION_TABLE
            . ' INNER JOIN ' . self::DATA_TABLE . ' ON obj_fi = obj_id WHERE question_id = %s AND type = "qpl"',
            ['integer'],
            [$question_id]
        );

        $row = $this->database->fetchObject($result);
        return $row->cnt === 0;
    }

    public function isUsedInRandomTest(int $question_id): bool
    {
        $result = $this->database->queryF(
            'SELECT COUNT(test_random_question_id) cnt'
            . ' FROM ' . self::TEST_RANDOM_QUESTION_TABLE
            . ' WHERE question_fi = %s',
            ['integer'],
            [$question_id]
        );

        $row = $this->database->fetchObject($result);
        return $row->cnt > 0;
    }

    public function originalQuestionExists(int $question_id): bool
    {
        $res = $this->database->queryF(
            'SELECT COUNT(dupl.question_id) cnt'
            . ' FROM ' . self::MAIN_QUESTION_TABLE . ' dupl'
            . ' INNER JOIN ' . self::MAIN_QUESTION_TABLE . ' orig'
            . ' ON orig.question_id = dupl.original_id'
            . ' WHERE dupl.question_id = %s',
            ['integer'],
            [$question_id]
        );
        $row = $this->database->fetchObject($res);

        return $row->cnt > 0;
    }

    public function getQuestionsMissingResultRecord(
        int $active_id,
        int $pass,
        array $question_ids
    ): array {
        $in_question_ids = $this->database->in('question_fi', $question_ids, false, 'integer');

        $result = $this->database->queryF(
            'SELECT question_fi'
            . ' FROM ' . self::TEST_RESULTS_TABLE
            . ' WHERE active_fi = %s'
            . ' AND pass = %s'
            . ' AND ' . $in_question_ids,
            ['integer', 'integer'],
            [$active_id, $pass]
        );

        $questions_having_result_record = [];

        while ($row = $this->database->fetchObject($result)) {
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
        $in_question_ids = $this->database->in('question_fi', $question_ids, false, 'integer');

        $result = $this->database->queryF(
            'SELECT COUNT(*) cnt'
            . ' FROM ' . self::TEST_RESULTS_TABLE
            . ' WHERE active_fi = %s'
            . ' AND pass = %s'
            . ' AND ' . $in_question_ids,
            ['integer', 'integer'],
            [$active_id, $pass]
        );

        $row = $this->database->fetchAssoc($result);
        return $row->cnt < count($question_ids);
    }

    public function isInActiveTest(int $obj_id): bool
    {
        $result = $this->database->query(
            'SELECT COUNT(user_fi) cnt FROM ' . self::TEST_TO_ACTIVE_USER_TABLE
            . ' JOIN ' . self::TEST_FIXED_QUESTION_TABLE
            . ' ON ' . self::TEST_FIXED_QUESTION_TABLE . '.test_fi = ' . self::TEST_TO_ACTIVE_USER_TABLE . '.test_fi '
            . ' JOIN ' . self::MAIN_QUESTION_TABLE
            . ' ON ' . self::MAIN_QUESTION_TABLE . '.question_id = ' . self::TEST_FIXED_QUESTION_TABLE . '.question_fi '
            . ' WHERE ' . self::MAIN_QUESTION_TABLE . '.obj_fi = ' . $this->database->quote($obj_id, 'integer')
        );

        $row = $this->database->fetchObject($result);
        return $row->cnt > 0;
    }

    public function questionTitleExistsInPool(int $questionpool_id, string $title): bool
    {
        $result = $this->database->queryF(
            'SELECT COUNT(*) cnt FROM ' . self::MAIN_QUESTION_TABLE
            . ' WHERE obj_fi = %s AND title = %s',
            ['integer','text'],
            [$questionpool_id, $title]
        );
        $row = $this->database->fetchObject($result);
        return $row->cnt > 0;
    }
}
