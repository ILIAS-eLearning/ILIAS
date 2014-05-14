<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionProcessLocker.php';
require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionProcessLockerNone.php';
require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionProcessLockerFile.php';
require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionProcessLockerDb.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilAssQuestionProcessLockerFactory
{
	/**
	 * @var ilSetting
	 */
	protected $settings;

	/**
	 * @var ilDB
	 */
	protected $db;

	/**
	 * @var integer
	 */
	protected $questionId;

	/**
	 * @var integer
	 */
	protected $userId;

	/**
	 * @param ilSetting $settings
	 * @param ilDB $db
	 */
	public function __construct(ilSetting $settings, ilDB $db)
	{
		$this->settings = $settings;
		$this->db = $db;
	}

	/**
	 * @param int $questionId
	 */
	public function setQuestionId($questionId)
	{
		$this->questionId = $questionId;
	}

	/**
	 * @return int
	 */
	public function getQuestionId()
	{
		return $this->questionId;
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
	public function getUserId()
	{
		return $this->userId;
	}
	
	private function getLockModeSettingValue()
	{
		return $this->settings->get('quest_process_lock_mode', ilAssQuestionProcessLocker::LOCK_MODE_NONE);
	}
	
	public function getLocker()
	{
		switch( $this->getLockModeSettingValue() )
		{
			case ilAssQuestionProcessLocker::LOCK_MODE_NONE:
				
				$locker = new ilAssQuestionProcessLockerNone();
				break;
				
			case ilAssQuestionProcessLocker::LOCK_MODE_FILE:

				require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionProcessLockFileStorage.php';
				$storage = new ilAssQuestionProcessLockFileStorage($this->getQuestionId(), $this->getUserId());
				$storage->create();

				$locker = new ilAssQuestionProcessLockerFile($storage);
				break;
			
			case ilAssQuestionProcessLocker::LOCK_MODE_DB:

				$locker = new ilAssQuestionProcessLockerDb($this->db);
				break;
		}
		
		return $locker;
	}
} 