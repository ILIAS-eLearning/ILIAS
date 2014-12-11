<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestProcessLocker.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestProcessLockerDb extends ilTestProcessLocker
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

	public function requestTestStartLockCheckLock()
	{
		$tables = array(
			array('name' => 'tst_active', 'type' => ilDB::LOCK_WRITE),
			array('name' => 'tst_active', 'type' => ilDB::LOCK_WRITE, 'sequence' => true)
		);

		$this->db->lockTables($tables);
	}

	public function releaseTestStartLockCheckLock()
	{
		$this->db->unlockTables();
	}
} 