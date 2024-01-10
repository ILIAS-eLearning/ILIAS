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

use ILIAS\Setup;
use ILIAS\Setup\Environment;
use ilDBInterface;
use ILIAS\Setup\CLI\IOWrapper;

class ilFixMissingQuestionDuplicationMigration implements Setup\Migration
{
    private ilDBInterface $db;

    /**
     * @var IOWrapper
     */
    private mixed $io;

    public function getLabel(): string
    {
        return 'Fix Missing Question Duplication When Creating in Test And Pool';
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return 10;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new \ilDatabaseInitializedObjective()
        ];
    }

    public function prepare(Environment $environment): void
    {
        //This is necessary optherwise questions cannot be copied
        ilContext::init(ilContext::CONTEXT_CRON);
        ilInitialisation::initILIAS();
        $this->db = $environment->getResource(Environment::RESOURCE_DATABASE);
    }

    public function step(Environment $environment): void
    {
        $query_main = 'SELECT qst.question_id FROM tst_tests AS tst'
            . '   INNER JOIN tst_test_question AS tst_qst'
            . '      ON tst.test_id = tst_qst.test_fi'
            . '   INNER JOIN qpl_questions AS qst'
            . '      ON tst_qst.question_fi = qst.question_id'
            . '   WHERE tst.question_set_type = "FIXED_QUEST_SET"'
            . '      AND qst.obj_fi != tst.obj_fi'
            . '      AND ISNULL(qst.original_id)';
        $result_main = $this->db->query($query_main);
        while ($question = $this->db->fetchAssoc($result_main)) {
            $question_obj = assQuestion::_instanciateQuestion((int) $question['question_id']);
            $clone_id = $question_obj->duplicate(false);

            $this->db->update(
                'qpl_questions',
                ['original_id' => ['integer', $clone_id]],
                ['original_id' => ['integer', $question['question_id']]]
            );

            $test_query = 'SELECT obj_fi FROM tst_test_question INNER JOIN tst_tests ON tst_test_question.test_fi = tst_tests.test_id WHERE question_fi = %s';
            $test_result = $this->db->queryF($test_query, ['integer'], [$question['question_id']]);

            $this->db->update(
                'qpl_questions',
                [
                    'original_id' => ['integer', $clone_id],
                    'obj_fi' => ['integer', ($this->db->fetchObject($test_result))->obj_fi]
                ],
                ['question_id' => ['integer', $question['question_id']]]
            );
        }
    }

    public function getRemainingAmountOfSteps(): int
    {
        $query = 'SELECT COUNT(qst.question_id) AS cnt FROM tst_tests AS tst'
            . '   INNER JOIN tst_test_question AS tst_qst'
            . '      ON tst.test_id = tst_qst.test_fi'
            . '   INNER JOIN qpl_questions AS qst'
            . '      ON tst_qst.question_fi = qst.question_id'
            . '   WHERE tst.question_set_type = "FIXED_QUEST_SET"'
            . '      AND qst.obj_fi != tst.obj_fi'
            . '      AND ISNULL(qst.original_id)';
        $result = $this->db->query($query);
        $row = $this->db->fetchAssoc($result);

        return (int) ($row['cnt'] ?? 0);
    }

}
