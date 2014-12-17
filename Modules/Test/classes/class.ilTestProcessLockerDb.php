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
			array('name' => 'tst_active', 'type' => ilDB::LOCK_WRITE)
		);

		$this->db->lockTables($tables);
	}

	public function releaseTestStartLockCheckLock()
	{
		$this->db->unlockTables();
	}

	public function requestRandomPassBuildLock($withTaxonomyTables = false)
	{
		$tables = array();
		
		$tables[] = array('name' => 'tst_rnd_cpy', 'type' => ilDB::LOCK_WRITE);
		$tables[] = array('name' => 'qpl_questions', 'type' => ilDB::LOCK_WRITE);
		$tables[] = array('name' => 'qpl_qst_type', 'type' => ilDB::LOCK_WRITE);
		$tables[] = array('name' => 'tst_test_rnd_qst', 'type' => ilDB::LOCK_WRITE);
		$tables[] = array('name' => 'tst_test_rnd_qst', 'type' => ilDB::LOCK_WRITE, 'sequence' => true);

		if( $withTaxonomyTables )
		{
			$tables[] = array('name' => 'tax_tree s', 'type' => ilDB::LOCK_WRITE);
			$tables[] = array('name' => 'tax_tree t', 'type' => ilDB::LOCK_WRITE);
			$tables[] = array('name' => 'tax_node_assignment', 'type' => ilDB::LOCK_WRITE);
		}

		$this->db->lockTables($tables);
	}

	public function releaseRandomPassBuildLock()
	{
		$this->db->unlockTables();
	}
} 