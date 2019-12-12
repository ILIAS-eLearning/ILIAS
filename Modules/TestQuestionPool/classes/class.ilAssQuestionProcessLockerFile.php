<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionProcessLocker.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilAssQuestionProcessLockerFile extends ilAssQuestionProcessLocker
{
    const PROCESS_NAME_QUESTION_WORKING_STATE_UPDATE = 'questionWorkingStateUpdate';
    
    /**
     * @var ilAssQuestionProcessLockFileStorage
     */
    protected $lockFileStorage;

    /**
     * @var resource
     */
    protected $lockFileHandles;

    /**
     * @param ilAssQuestionProcessLockFileStorage $lockFileStorage
     */
    public function __construct(ilAssQuestionProcessLockFileStorage $lockFileStorage)
    {
        $this->lockFileStorage = $lockFileStorage;
        $this->lockFileHandles = array();
    }

    /**
     * {@inheritdoc}
     */
    protected function onBeforeExecutingPersistWorkingStateOperation()
    {
        $this->requestLock(self::PROCESS_NAME_QUESTION_WORKING_STATE_UPDATE);
    }

    /**
     * {@inheritdoc}
     */
    protected function onAfterExecutingPersistWorkingStateOperation()
    {
        $this->releaseLock(self::PROCESS_NAME_QUESTION_WORKING_STATE_UPDATE);
    }

    /**
     * {@inheritdoc}
     */
    protected function onBeforeExecutingUserSolutionAdoptOperation()
    {
        $this->requestLock(self::PROCESS_NAME_QUESTION_WORKING_STATE_UPDATE);
    }

    /**
     * {@inheritdoc}
     */
    protected function onAfterExecutingUserSolutionAdoptOperation()
    {
        $this->releaseLock(self::PROCESS_NAME_QUESTION_WORKING_STATE_UPDATE);
    }

    /**
     * @param string $processName
     */
    private function requestLock($processName)
    {
        $lockFilePath = $this->getLockFilePath($processName);
        $this->lockFileHandles[$processName] = fopen($lockFilePath, 'w');
        flock($this->lockFileHandles[$processName], LOCK_EX);
    }

    /**
     * @param string $processName
     * @return string
     */
    private function getLockFilePath($processName)
    {
        $path = $this->lockFileStorage->getPath();
        return $path . '/' . $processName . '.lock';
    }

    /**
     * @param string $processName
     */
    private function releaseLock($processName)
    {
        flock($this->lockFileHandles[$processName], LOCK_UN);
        fclose($this->lockFileHandles[$processName]);
    }
}
