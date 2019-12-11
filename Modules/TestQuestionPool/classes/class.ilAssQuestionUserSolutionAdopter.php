<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/TestQuestionPool
 */
class ilAssQuestionUserSolutionAdopter
{
    /**
     * @var ressource
     */
    protected static $preparedDeleteSolutionRecordsStatement = null;

    /**
     * @var ressource
     */
    protected static $preparedSelectSolutionRecordsStatement = null;

    /**
     * @var ressource
     */
    protected static $preparedInsertSolutionRecordStatement = null;

    /**
     * @var ressource
     */
    protected static $preparedDeleteResultRecordStatement = null;

    /**
     * @var ressource
     */
    protected static $preparedSelectResultRecordStatement = null;

    /**
     * @var ressource
     */
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
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return int
     */
    public function getActiveId()
    {
        return $this->activeId;
    }

    /**
     * @param int $activeId
     */
    public function setActiveId($activeId)
    {
        $this->activeId = $activeId;
    }

    /**
     * @return int
     */
    public function getTargetPass()
    {
        return $this->targetPass;
    }

    /**
     * @param int $targetPass
     */
    public function setTargetPass($targetPass)
    {
        $this->targetPass = $targetPass;
    }

    /**
     * @return array
     */
    public function getQuestionIds()
    {
        return $this->questionIds;
    }

    /**
     * @param array $questionIds
     */
    public function setQuestionIds($questionIds)
    {
        $this->questionIds = $questionIds;
    }
    
    public function perform()
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

    protected function adoptQuestionAnswer($questionId)
    {
        $this->resetTargetSolution($questionId);
        $this->resetTargetResult($questionId);

        $sourcePass = $this->adoptSourceSolution($questionId);

        if ($sourcePass !== null) {
            $this->adoptSourceResult($questionId, $sourcePass);
        }
    }

    protected function resetTargetSolution($questionId)
    {
        $this->db->execute(
            $this->getPreparedDeleteSolutionRecordsStatement(),
            array($this->getActiveId(), $questionId, $this->getTargetPass())
        );
    }

    protected function resetTargetResult($questionId)
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
    
    protected function adoptSourceResult($questionId, $sourcePass)
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

    protected function getPreparedDeleteSolutionRecordsStatement()
    {
        if (self::$preparedDeleteSolutionRecordsStatement === null) {
            self::$preparedDeleteSolutionRecordsStatement = $this->db->prepareManip(
                "DELETE FROM tst_solutions WHERE active_fi = ? AND question_fi = ? AND pass = ?",
                array('integer', 'integer', 'integer')
            );
        }

        return self::$preparedDeleteSolutionRecordsStatement;
    }

    protected function getPreparedSelectSolutionRecordsStatement()
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

    protected function getPreparedInsertSolutionRecordStatement()
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

    protected function getPreparedDeleteResultRecordStatement()
    {
        if (self::$preparedDeleteResultRecordStatement === null) {
            self::$preparedDeleteResultRecordStatement = $this->db->prepareManip(
                "DELETE FROM tst_test_result WHERE active_fi = ? AND question_fi = ? AND pass = ?",
                array('integer', 'integer', 'integer')
            );
        }

        return self::$preparedDeleteResultRecordStatement;
    }

    protected function getPreparedSelectResultRecordStatement()
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

    protected function getPreparedInsertResultRecordStatement()
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
