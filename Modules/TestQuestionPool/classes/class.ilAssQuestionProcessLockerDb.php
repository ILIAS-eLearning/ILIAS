<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionProcessLocker.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilAssQuestionProcessLockerDb extends ilAssQuestionProcessLocker
{
	/**
	 * @var ilDB
	 */
	protected $db;

	/**
	 * @param ilDB $db
	 */
	public function __construct(ilDB $db)
	{
		$this->db = $db;
	}

	public function requestUserSolutionUpdateLock()
	{
		$this->db->lockTables(array(
			array('name' => 'tst_solutions', 'type' => ilDB::LOCK_WRITE),
			array('name' => 'tst_solutions', 'type' => ilDB::LOCK_WRITE, 'sequence' => true)
		));
	}

	public function releaseUserSolutionUpdateLock()
	{
		$this->db->unlockTables();
	}

	public function requestUserQuestionResultUpdateLock()
	{
		$this->db->lockTables(array(
			array('name' => 'tst_test_result', 'type' => ilDB::LOCK_WRITE),
			array('name' => 'tst_test_result', 'type' => ilDB::LOCK_WRITE, 'sequence' => true)
		));
	}

	public function releaseUserQuestionResultUpdateLock()
	{
		$this->db->unlockTables();
	}

	public function requestUserPassResultUpdateLock()
	{
		// no lock neccessary, because a single replace query is used
		
		//$this->db->lockTables(array(
		//	array('name' => 'tst_pass_result', 'type' => ilDB::LOCK_WRITE)
		//));
	}

	public function releaseUserPassResultUpdateLock()
	{
		// no lock neccessary, because a single replace query is used

		//$this->db->unlockTables();
	}

	public function requestUserTestResultUpdateLock()
	{
		$this->db->lockTables(array(
			array('name' => 'tst_result_cache', 'type' => ilDB::LOCK_WRITE)
		));
	}

	public function releaseUserTestResultUpdateLock()
	{
		$this->db->unlockTables();
	}
} 