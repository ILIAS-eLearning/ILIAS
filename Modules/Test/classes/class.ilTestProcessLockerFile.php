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
class ilTestProcessLockerFile extends ilTestProcessLocker
{
    public const PROCESS_NAME_TEST_START_LOCK_CHECK = 'testStartLockCheck';
    public const PROCESS_NAME_RANDOM_PASS_BUILD = 'randomPassBuild';
    public const PROCESS_NAME_TEST_FINISH = 'testFinish';

    protected ilTestProcessLockFileStorage $lockFileStorage;

    /**
     * @var resource
     */
    protected $lockFileHandles;

    /**
     * ilTestProcessLockerFile constructor.
     * @param ilTestProcessLockFileStorage $lockFileStorage
     */
    public function __construct(ilTestProcessLockFileStorage $lockFileStorage)
    {
        $this->lockFileStorage = $lockFileStorage;
        $this->lockFileHandles = array();
    }

    /**
     * {@inheritdoc}
     */
    protected function onBeforeExecutingTestStartOperation()
    {
        parent::onBeforeExecutingTestStartOperation();
        $this->requestLock(self::PROCESS_NAME_TEST_START_LOCK_CHECK);
    }

    /**
     * {@inheritdoc}
     */
    protected function onAfterExecutingTestStartOperation()
    {
        $this->releaseLock(self::PROCESS_NAME_TEST_START_LOCK_CHECK);
        parent::onAfterExecutingTestStartOperation();
    }

    /**
     * {@inheritdoc}
     */
    protected function onBeforeExecutingRandomPassBuildOperation($withTaxonomyTables = false)
    {
        parent::onBeforeExecutingRandomPassBuildOperation($withTaxonomyTables);
        $this->requestLock(self::PROCESS_NAME_RANDOM_PASS_BUILD);
    }

    /**
     * {@inheritdoc}
     */
    protected function onAfterExecutingRandomPassBuildOperation($withTaxonomyTables = false)
    {
        $this->releaseLock(self::PROCESS_NAME_RANDOM_PASS_BUILD);
        parent::onAfterExecutingRandomPassBuildOperation($withTaxonomyTables);
    }

    /**
     * {@inheritdoc}
     */
    protected function onBeforeExecutingTestFinishOperation()
    {
        parent::onBeforeExecutingTestStartOperation();
        $this->requestLock(self::PROCESS_NAME_TEST_FINISH);
    }

    /**
     * {@inheritdoc}
     */
    protected function onAfterExecutingTestFinishOperation()
    {
        $this->releaseLock(self::PROCESS_NAME_TEST_FINISH);
        parent::onAfterExecutingTestStartOperation();
    }

    protected function onBeforeExecutingNamedOperation(string $operationDescriptor): void
    {
        $this->requestLock($operationDescriptor);
        parent::onBeforeExecutingNamedOperation($operationDescriptor);
    }

    protected function onAfterExecutingNamedOperation(string $operationDescriptor): void
    {
        $this->releaseLock($operationDescriptor);
        parent::onAfterExecutingNamedOperation($operationDescriptor);
    }

    private function requestLock($processName)
    {
        $lockFilePath = $this->getLockFilePath($processName);
        $this->lockFileHandles[$processName] = fopen($lockFilePath, 'w');
        flock($this->lockFileHandles[$processName], LOCK_EX);
    }

    private function getLockFilePath($processName): string
    {
        $path = $this->lockFileStorage->getAbsolutePath();
        return $path . '/' . $processName . '.lock';
    }

    private function releaseLock($processName)
    {
        flock($this->lockFileHandles[$processName], LOCK_UN);
        fclose($this->lockFileHandles[$processName]);
    }
}
