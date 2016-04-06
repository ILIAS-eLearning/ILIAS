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
	 * @var ilDBInterface
	 */
	protected $db;

	/**
	 * @param ilDBInterface $db
	 */
	public function __construct(ilDBInterface $db)
	{
		$this->db = $db;
	}

	public function requestTestStartLockCheckLock()
	{
		$tables = array(
			array('name' => 'tst_active', 'type' => ilDBConstants::LOCK_WRITE)
		);

		$this->db->lockTables($tables);
	}

	public function releaseTestStartLockCheckLock()
	{
		$this->db->unlockTables();
	}

	/**
	 * {@inheritdoc}
	 */
	public function requestRandomPassBuildLock($withTaxonomyTables = false)
	{
		$tables = array();
		
		$tables[] = array('name' => 'tst_rnd_cpy', 'type' => ilDBConstants::LOCK_WRITE);
		$tables[] = array('name' => 'qpl_questions', 'type' => ilDBConstants::LOCK_WRITE);
		$tables[] = array('name' => 'qpl_qst_type', 'type' => ilDBConstants::LOCK_WRITE);
		$tables[] = array('name' => 'tst_test_rnd_qst', 'type' => ilDBConstants::LOCK_WRITE);
		$tables[] = array('name' => 'tst_test_rnd_qst', 'type' => ilDBConstants::LOCK_WRITE, 'sequence' => true);
		$tables[] = array('name' => 'il_pluginslot', 'type' => ilDBConstants::LOCK_WRITE);
		$tables[] = array('name' => 'il_plugin', 'type' => ilDBConstants::LOCK_WRITE);

		if( $withTaxonomyTables )
		{
			$tables[] = array('name' => 'tax_tree s', 'type' => ilDBConstants::LOCK_WRITE);
			$tables[] = array('name' => 'tax_tree t', 'type' => ilDBConstants::LOCK_WRITE);
			$tables[] = array('name' => 'tax_node_assignment', 'type' => ilDBConstants::LOCK_WRITE);
		}

		$this->db->lockTables($tables);
	}

	public function releaseRandomPassBuildLock()
	{
		$this->db->unlockTables();
	}
} 