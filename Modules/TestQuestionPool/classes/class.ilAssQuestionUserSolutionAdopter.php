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

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/TestQuestionPool
 */
class ilAssQuestionUserSolutionAdopter
{
    protected static $preparedDeleteSolutionRecordsStatement = null;

    protected static $preparedSelectSolutionRecordsStatement = null;

    protected static $preparedInsertSolutionRecordStatement = null;

    protected static $preparedDeleteResultRecordStatement = null;

    protected static $preparedSelectResultRecordStatement = null;

    protected static $preparedInsertResultRecordStatement = null;

    /**
     * @var ilDBInterface
     */
    protected $db;

    /**
     * @var ilAssQuestionProcessLockerFactory
     */
    protected $processLockerFactory;

    /**
     * @var integer
     */
    protected $userId;

    /**
     * @var integer
     */
    protected $activeId;

    /**
     * @var integer
     */
    protected $targetPass;

    /**
     * @var array
     */
    protected $questionIds;

    /**
     * @param ilDBInterface $db
     * @param ilSetting $assSettings
     * @param bool $isAssessmentLogEnabled
     */
    public function __construct(ilDBInterface $db, ilSetting $assSettings, $isAssessmentLogEnabled)
    {
        $this->db = $db;

        $this->userId = null;
        $this->activeId = null;
        $this->targetPass = null;
        $this->questionIds = array();

        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionProcessLockerFactory.php';
        $this->processLockerFactory = new ilAssQuestionProcessLockerFactory($assSettings, $db);
        $this->processLockerFactory->setAssessmentLogEnabled($isAssessmentLogEnabled);
    }

    /**
     * @return int
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId($userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @return int
     */
    public function getActiveId(): ?int
    {
        return $this->activeId;
    }

    /**
     * @param int $activeId
     */
    public function setActiveId($activeId): void
    {
        $this->activeId = $activeId;
    }

    /**
     * @return int
     */
    public function getTargetPass(): ?int
    {
        return $this->targetPass;
    }

    /**
     * @param int $targetPass
     */
    public function setTargetPass($targetPass): void
    {
        $this->targetPass = $targetPass;
    }

    /**
     * @return array
     */
    public function getQuestionIds(): array
    {
        return $this->questionIds;
    }

    /**
     * @param array $questionIds
     */
    public function setQuestionIds($questionIds): void
    {
        $this->questionIds = $questionIds;
    }

    public function perform(): void
    {
        $this->processLockerFactory->setUserId($this->getUserId());

        foreach ($this->getQuestionIds() as $questionId) {
            $this->processLockerFactory->setQuestionId($questionId);
            $processLocker = $this->processLockerFactory->getLocker();

            $processLocker->executeUserTestResultUpdateLockOperation(function () use ($questionId) {
                $this->adoptQuestionAnswer($questionId);
            });
        }
    }

    protected function adoptQuestionAnswer($questionId): void
    {
        $this->resetTargetSolution($questionId);
        $this->resetTargetResult($questionId);

        $sourcePass = $this->adoptSourceSolution($questionId);

        if ($sourcePass !== null) {
            $this->adoptSourceResult($questionId, $sourcePass);
        }
    }

    protected function resetTargetSolution($questionId): void
    {
        $this->db->execute(
            $this->getPreparedDeleteSolutionRecordsStatement(),
            array($this->getActiveId(), $questionId, $this->getTargetPass())
        );
    }

    protected function resetTargetResult($questionId): void
    {
        $this->db->execute(
            $this->getPreparedDeleteResultRecordStatement(),
            array($this->getActiveId(), $questionId, $this->getTargetPass())
        );
    }

    protected function adoptSourceSolution($questionId)
    {
        $res = $this->db->execute(
            $this->getPreparedSelectSolutionRecordsStatement(),
            array($this->getActiveId(), $questionId, $this->getTargetPass())
        );

        $sourcePass = null;

        while ($row = $this->db->fetchAssoc($res)) {
            if ($sourcePass === null) {
                $sourcePass = $row['pass'];
            } elseif ($row['pass'] < $sourcePass) {
                break;
            }

            $solutionId = $this->db->nextId('tst_solutions');

            $this->db->execute($this->getPreparedInsertSolutionRecordStatement(), array(
                $solutionId, $this->getActiveId(), $questionId, $this->getTargetPass(), time(),
                $row['points'], $row['value1'], $row['value2']
            ));
        }

        return $sourcePass;
    }

    protected function adoptSourceResult($questionId, $sourcePass): void
    {
        $res = $this->db->execute(
            $this->getPreparedSelectResultRecordStatement(),
            array($this->getActiveId(), $questionId, $sourcePass)
        );

        $row = $this->db->fetchAssoc($res);

        $resultId = $this->db->nextId('tst_test_result');

        $this->db->execute($this->getPreparedInsertResultRecordStatement(), array(
            $resultId, $this->getActiveId(), $questionId, $this->getTargetPass(), time(),
            $row['points'], $row['manual'], $row['hint_count'], $row['hint_points'], $row['answered']
        ));
    }

    protected function getPreparedDeleteSolutionRecordsStatement(): ilDBStatement
    {
        if (self::$preparedDeleteSolutionRecordsStatement === null) {
            self::$preparedDeleteSolutionRecordsStatement = $this->db->prepareManip(
                "DELETE FROM tst_solutions WHERE active_fi = ? AND question_fi = ? AND pass = ?",
                array('integer', 'integer', 'integer')
            );
        }

        return self::$preparedDeleteSolutionRecordsStatement;
    }

    protected function getPreparedSelectSolutionRecordsStatement(): ilDBStatement
    {
        if (self::$preparedSelectSolutionRecordsStatement === null) {
            $query = "
				SELECT pass, points, value1, value2 FROM tst_solutions
				WHERE active_fi = ? AND question_fi = ? AND pass < ? ORDER BY pass DESC
			";

            self::$preparedSelectSolutionRecordsStatement = $this->db->prepare(
                $query,
                array('integer', 'integer', 'integer')
            );
        }

        return self::$preparedSelectSolutionRecordsStatement;
    }

    protected function getPreparedInsertSolutionRecordStatement(): ilDBStatement
    {
        if (self::$preparedInsertSolutionRecordStatement === null) {
            $query = "
				INSERT INTO tst_solutions (
					solution_id, active_fi, question_fi, pass, tstamp, points, value1, value2
				) VALUES (
					?, ?, ?, ?, ?, ?, ?, ?
				)
			";

            self::$preparedInsertSolutionRecordStatement = $this->db->prepareManip(
                $query,
                array('integer', 'integer', 'integer', 'integer', 'integer', 'integer', 'text', 'text')
            );
        }

        return self::$preparedInsertSolutionRecordStatement;
    }

    protected function getPreparedDeleteResultRecordStatement(): ilDBStatement
    {
        if (self::$preparedDeleteResultRecordStatement === null) {
            self::$preparedDeleteResultRecordStatement = $this->db->prepareManip(
                "DELETE FROM tst_test_result WHERE active_fi = ? AND question_fi = ? AND pass = ?",
                array('integer', 'integer', 'integer')
            );
        }

        return self::$preparedDeleteResultRecordStatement;
    }

    protected function getPreparedSelectResultRecordStatement(): ilDBStatement
    {
        if (self::$preparedSelectResultRecordStatement === null) {
            $query = "
				SELECT points, manual, hint_count, hint_points, answered FROM tst_test_result
				WHERE active_fi = ? AND question_fi = ? AND pass = ?
			";

            self::$preparedSelectResultRecordStatement = $this->db->prepare(
                $query,
                array('integer', 'integer', 'integer')
            );
        }

        return self::$preparedSelectResultRecordStatement;
    }

    protected function getPreparedInsertResultRecordStatement(): ilDBStatement
    {
        if (self::$preparedInsertResultRecordStatement === null) {
            $query = "
				INSERT INTO tst_test_result (
					test_result_id, active_fi, question_fi, pass, tstamp,
					points, manual, hint_count, hint_points, answered
				) VALUES (
					?, ?, ?, ?, ?, ?, ?, ?, ?, ?
				)
			";

            self::$preparedInsertResultRecordStatement = $this->db->prepareManip(
                $query,
                array('integer', 'integer', 'integer', 'integer', 'integer', 'integer', 'integer', 'integer', 'integer', 'integer')
            );
        }

        return self::$preparedInsertResultRecordStatement;
    }
}
