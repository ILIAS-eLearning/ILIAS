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
	 * @param ilAssQuestionProcessLockFileStorage $lockFileStorage
	 */
	public function __construct(ilTestProcessLockFileStorage $lockFileStorage)
	{
		$this->lockFileStorage = $lockFileStorage;
		$this->lockFileHandles = array();
	}

	public function requestTestStartLockCheckLock()
	{
		$this->requestLock(self::PROCESS_NAME_TEST_START_LOCK_CHECK);
	}

	public function releaseTestStartLockCheckLock()
	{
		$this->releaseLock(self::PROCESS_NAME_TEST_START_LOCK_CHECK);
	}

	public function requestRandomPassBuildLock()
	{
		$this->requestLock(self::PROCESS_NAME_RANDOM_PASS_BUILD);
	}

	public function releaseRandomPassBuildLock()
	{
		$this->releaseLock(self::PROCESS_NAME_RANDOM_PASS_BUILD);
	}
	
	private function requestLock($processName)
	{
		$lockFilePath = $this->getLockFilePath($processName);
		$this->lockFileHandles[$processName] = fopen($lockFilePath, 'w');
		flock($this->lockFileHandles[$processName], LOCK_EX);
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