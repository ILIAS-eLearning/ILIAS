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
 * @package     Modules/Test
 */
class ilAssQuestionProcessLockerFile extends ilAssQuestionProcessLocker
{
    public const PROCESS_NAME_QUESTION_WORKING_STATE_UPDATE = 'questionWorkingStateUpdate';

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
    protected function onBeforeExecutingPersistWorkingStateOperation(): void
    {
        $this->requestLock(self::PROCESS_NAME_QUESTION_WORKING_STATE_UPDATE);
    }

    /**
     * {@inheritdoc}
     */
    protected function onAfterExecutingPersistWorkingStateOperation(): void
    {
        $this->releaseLock(self::PROCESS_NAME_QUESTION_WORKING_STATE_UPDATE);
    }

    /**
     * {@inheritdoc}
     */
    protected function onBeforeExecutingUserSolutionAdoptOperation(): void
    {
        $this->requestLock(self::PROCESS_NAME_QUESTION_WORKING_STATE_UPDATE);
    }

    /**
     * {@inheritdoc}
     */
    protected function onAfterExecutingUserSolutionAdoptOperation(): void
    {
        $this->releaseLock(self::PROCESS_NAME_QUESTION_WORKING_STATE_UPDATE);
    }

    /**
     * @param string $processName
     */
    private function requestLock($processName): void
    {
        $lockFilePath = $this->getLockFilePath($processName);
        $this->lockFileHandles[$processName] = fopen($lockFilePath, 'w');
        flock($this->lockFileHandles[$processName], LOCK_EX);
    }

    /**
     * @param string $processName
     * @return string
     */
    private function getLockFilePath($processName): string
    {
        $path = $this->lockFileStorage->getPath();
        return $path . '/' . $processName . '.lock';
    }

    /**
     * @param string $processName
     */
    private function releaseLock($processName): void
    {
        flock($this->lockFileHandles[$processName], LOCK_UN);
        fclose($this->lockFileHandles[$processName]);
    }
}
