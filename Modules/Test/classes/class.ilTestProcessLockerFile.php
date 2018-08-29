<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionProcessLocker.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestProcessLockerFile extends ilTestProcessLocker
{
	const PROCESS_NAME_TEST_START_LOCK_CHECK = 'testStartLockCheck';
	const PROCESS_NAME_RANDOM_PASS_BUILD = 'randomPassBuild';
	
	/**
	 * @var ilTestProcessLockFileStorage
	 */
	protected $lockFileStorage;

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

	private function requestLock($processName)
	{
		$lockFilePath = $this->getLockFilePath($processName);
		$this->lockFileHandles[$processName] = fopen($lockFilePath, 'w');
		if ($this->lockFileHandles[$processName] === false) {
			throw new ilTestException("could not open lock file");
		}
		if (flock($this->lockFileHandles[$processName], LOCK_EX) !== true) {
			throw new ilTestException("could not acquire file lock");
		}
	}
	
	private function getLockFilePath($processName)
	{
		$path = $this->lockFileStorage->getPath();
		return $path.'/'.$processName.'.lock';
	}
	
	private function releaseLock($processName)
	{
		flock($this->lockFileHandles[$processName], LOCK_UN);
		fclose($this->lockFileHandles[$processName]);
	}
}