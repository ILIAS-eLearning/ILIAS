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

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package components\ILIAS/TestQuestionPool
 */
class ilAssQuestionUserSolutionAdopter
{
    protected static ?ilDBStatement $prepared_delete_solution_records_statement = null;
    protected static ?ilDBStatement $prepared_select_solution_records_statement = null;
    protected static ?ilDBStatement $prepared_insert_solution_record_statement = null;
    protected static ?ilDBStatement $prepared_delete_result_record_statement = null;
    protected static ?ilDBStatement $prepared_select_result_record_statement = null;
    protected static ?ilDBStatement $prepared_insert_result_record_statement = null;

    private ilAssQuestionProcessLockerFactory $process_locker_factory;
    private ?int $user_id = null;
    protected ?int $active_id = null;
    protected ?int $target_pass = null;

    /**
     * @var array<int>
     */
    protected array $question_ids = [];

    public function __construct(
        private ilDBInterface $db,
        ilSetting $ass_settings
    ) {
        $this->process_locker_factory = new ilAssQuestionProcessLockerFactory($ass_settings, $db);
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    public function getActiveId(): ?int
    {
        return $this->active_id;
    }

    public function setActiveId(int $active_id): void
    {
        $this->active_id = $active_id;
    }

    public function getTargetPass(): ?int
    {
        return $this->target_pass;
    }

    public function setTargetPass(int $target_pass): void
    {
        $this->target_pass = $target_pass;
    }

    public function getQuestionIds(): array
    {
        return $this->question_ids;
    }

    /**
     * @param array<int> $question_ids
     */
    public function setQuestionIds(array $question_ids): void
    {
        $this->question_ids = $question_ids;
    }

    public function perform(): void
    {
        $this->process_locker_factory->setUserId($this->getUserId());

        foreach ($this->getQuestionIds() as $question_id) {
            $this->process_locker_factory->setQuestionId($question_id);
            $processLocker = $this->process_locker_factory->getLocker();

            $processLocker->executeUserTestResultUpdateLockOperation(function () use ($question_id) {
                $this->adoptQuestionAnswer($question_id);
            });
        }
    }

    protected function adoptQuestionAnswer(int $question_id): void
    {
        $this->resetTargetSolution($question_id);
        $this->resetTargetResult($question_id);

        $source_pass = $this->adoptSourceSolution($question_id);

        if ($source_pass !== null) {
            $this->adoptSourceResult($question_id, $source_pass);
        }
    }

    protected function resetTargetSolution(int $question_id): void
    {
        $this->db->execute(
            $this->getPreparedDeleteSolutionRecordsStatement(),
            [$this->getActiveId(), $question_id, $this->getTargetPass()]
        );
    }

    protected function resetTargetResult(int $question_id): void
    {
        $this->db->execute(
            $this->getPreparedDeleteResultRecordStatement(),
            [$this->getActiveId(), $question_id, $this->getTargetPass()]
        );
    }

    protected function adoptSourceSolution(int $question_id): ?int
    {
        $res = $this->db->execute(
            $this->getPreparedSelectSolutionRecordsStatement(),
            [$this->getActiveId(), $question_id, $this->getTargetPass()]
        );

        $source_pass = null;

        while ($row = $this->db->fetchAssoc($res)) {
            if ($source_pass === null) {
                $source_pass = $row['pass'];
            } elseif ($row['pass'] < $source_pass) {
                break;
            }

            $solution_id = $this->db->nextId('tst_solutions');

            $this->db->execute($this->getPreparedInsertSolutionRecordStatement(), [
                $solution_id, $this->getActiveId(), $question_id, $this->getTargetPass(), time(),
                $row['points'], $row['value1'], $row['value2']
            ]);
        }

        return $source_pass;
    }

    protected function adoptSourceResult(int $question_id, int $source_pass): void
    {
        $res = $this->db->execute(
            $this->getPreparedSelectResultRecordStatement(),
            [$this->getActiveId(), $question_id, $source_pass]
        );

        $row = $this->db->fetchAssoc($res);

        $result_id = $this->db->nextId('tst_test_result');

        $this->db->execute($this->getPreparedInsertResultRecordStatement(), [
            $result_id, $this->getActiveId(), $question_id, $this->getTargetPass(), time(),
            $row['points'], $row['manual'], $row['hint_count'], $row['hint_points'], $row['answered']
        ]);
    }

    protected function getPreparedDeleteSolutionRecordsStatement(): ilDBStatement
    {
        if (self::$prepared_delete_solution_records_statement === null) {
            self::$prepared_delete_solution_records_statement = $this->db->prepareManip(
                "DELETE FROM tst_solutions WHERE active_fi = ? AND question_fi = ? AND pass = ?",
                ['integer', 'integer', 'integer']
            );
        }

        return self::$prepared_delete_solution_records_statement;
    }

    protected function getPreparedSelectSolutionRecordsStatement(): ilDBStatement
    {
        if (self::$prepared_select_solution_records_statement === null) {
            $query = "
				SELECT pass, points, value1, value2 FROM tst_solutions
				WHERE active_fi = ? AND question_fi = ? AND pass < ? ORDER BY pass DESC
			";

            self::$prepared_select_solution_records_statement = $this->db->prepare(
                $query,
                ['integer', 'integer', 'integer']
            );
        }

        return self::$prepared_select_solution_records_statement;
    }

    protected function getPreparedInsertSolutionRecordStatement(): ilDBStatement
    {
        if (self::$prepared_insert_solution_record_statement === null) {
            $query = "
				INSERT INTO tst_solutions (
					solution_id, active_fi, question_fi, pass, tstamp, points, value1, value2
				) VALUES (
					?, ?, ?, ?, ?, ?, ?, ?
				)
			";

            self::$prepared_insert_solution_record_statement = $this->db->prepareManip(
                $query,
                ['integer', 'integer', 'integer', 'integer', 'integer', 'integer', 'text', 'text']
            );
        }

        return self::$prepared_insert_solution_record_statement;
    }

    protected function getPreparedDeleteResultRecordStatement(): ilDBStatement
    {
        if (self::$prepared_delete_result_record_statement === null) {
            self::$prepared_delete_result_record_statement = $this->db->prepareManip(
                "DELETE FROM tst_test_result WHERE active_fi = ? AND question_fi = ? AND pass = ?",
                ['integer', 'integer', 'integer']
            );
        }

        return self::$prepared_delete_result_record_statement;
    }

    protected function getPreparedSelectResultRecordStatement(): ilDBStatement
    {
        if (self::$prepared_select_result_record_statement === null) {
            $query = "
				SELECT points, manual, hint_count, hint_points, answered FROM tst_test_result
				WHERE active_fi = ? AND question_fi = ? AND pass = ?
			";

            self::$prepared_select_result_record_statement = $this->db->prepare(
                $query,
                ['integer', 'integer', 'integer']
            );
        }

        return self::$prepared_select_result_record_statement;
    }

    protected function getPreparedInsertResultRecordStatement(): ilDBStatement
    {
        if (self::$prepared_insert_result_record_statement === null) {
            $query = "
				INSERT INTO tst_test_result (
					test_result_id, active_fi, question_fi, pass, tstamp,
					points, manual, hint_count, hint_points, answered
				) VALUES (
					?, ?, ?, ?, ?, ?, ?, ?, ?, ?
				)
			";

            self::$prepared_insert_result_record_statement = $this->db->prepareManip(
                $query,
                ['integer', 'integer', 'integer', 'integer', 'integer', 'integer', 'integer', 'integer', 'integer', 'integer']
            );
        }

        return self::$prepared_insert_result_record_statement;
    }
}
