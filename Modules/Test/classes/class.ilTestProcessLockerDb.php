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
	 * @var ilAtomQuery
	 */
	protected $atom_query;

	/**
	 * @param ilDBInterface $db
	 */
	public function __construct(ilDBInterface $db)
	{
		$this->db         = $db;
		$this->atom_query = $this->db->buildAtomQuery(); 
	}

	/**
	 * {@inheritdoc}
	 */
	protected function onBeforeExecutingTestStartOperation()
	{
		parent::onBeforeExecutingTestStartOperation();
		$this->atom_query->lockTable('tst_active');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function onBeforeExecutingRandomPassBuildOperation($withTaxonomyTables = false)
	{
		parent::onBeforeExecutingTestStartOperation();

		$this->atom_query->lockTable('tst_rnd_cpy');
		$this->atom_query->lockTable('qpl_questions');
		$this->atom_query->lockTable('qpl_qst_type');
		$this->atom_query->lockTable('tst_test_rnd_qst', true);
		$this->atom_query->lockTable('il_plugin');
		$this->atom_query->lockTable('tst_active');

		if($withTaxonomyTables)
		{
			$this->atom_query->lockTable('tax_tree s');
			$this->atom_query->lockTable('tax_tree t');
			$this->atom_query->lockTable('tax_node_assignment');
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function executeOperation(callable $operation)
	{
		$this->atom_query ->addQueryCallable(function(ilDBInterface $ilDB) use ($operation) {
			$operation();
		});
		$this->atom_query->run();
	}
} 