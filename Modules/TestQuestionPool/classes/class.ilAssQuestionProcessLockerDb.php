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
	 * @var ilDBInterface
	 */
	protected $db;

	/**
	 * @var ilAtomQuery
	 */
	protected $atom_query;

	private $assessmentLogEnabled = false;

	/**
	 * @param ilDBInterface $db
	 */
	public function __construct(ilDBInterface $db)
	{
		$this->db         = $db;
		$this->atom_query = $this->db->buildAtomQuery();
	}

	public function isAssessmentLogEnabled()
	{
		return $this->assessmentLogEnabled;
	}

	public function setAssessmentLogEnabled($assessmentLogEnabled)
	{
		$this->assessmentLogEnabled = $assessmentLogEnabled;
	}

	/**
	 * @return array
	 */
	private function getTablesUsedDuringAssessmentLog()
	{
		return array(
			array('name' => 'qpl_questions', 'sequence' => false),
			array('name' => 'tst_tests', 'sequence' => false),
			array('name' => 'tst_active', 'sequence' => false),
			array('name' => 'ass_log', 'sequence' => true)
		);
	}

	/**
	 * @return array
	 */
	private function getTablesUsedDuringSolutionUpdate()
	{
		return array(
			array('name' => 'tst_solutions', 'sequence' => true)
		);
	}

	/**
	 * @return array
	 */
	private function getTablesUsedDuringResultUpdate()
	{
		return array(
			array('name' => 'tst_test_result', 'sequence' => true)
		);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function onBeforeExecutingUserSolutionUpdateOperation()
	{
		parent::onBeforeExecutingUserSolutionUpdateOperation();
		$tables = $this->getTablesUsedDuringSolutionUpdate();

		if($this->isAssessmentLogEnabled())
		{
			$tables = array_merge($tables, $this->getTablesUsedDuringAssessmentLog());
		}

		foreach($tables as $table)
		{
			$this->atom_query->lockTable($table['name'], (bool)$table['sequence']);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function onBeforeExecutingUserQuestionResultUpdateOperation()
	{
		parent::onBeforeExecutingUserQuestionResultUpdateOperation();
		foreach($this->getTablesUsedDuringResultUpdate() as $table)
		{
			$this->atom_query->lockTable($table['name'], (bool)$table['sequence']);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function onBeforeExecutingUserSolutionAdoptOperation()
	{
		parent::onBeforeExecutingUserSolutionAdoptOperation();
		foreach(array_merge(
					$this->getTablesUsedDuringSolutionUpdate(), $this->getTablesUsedDuringResultUpdate()
				) as $table)
		{
			$this->atom_query->lockTable($table['name'], (bool)$table['sequence']);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function onBeforeExecutingUserTestResultUpdateOperation()
	{
		parent::onBeforeExecutingUserTestResultUpdateOperation();
		$this->atom_query->lockTable('tst_result_cache');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function executeOperation(callable $operation)
	{
		$this->atom_query->replaceQueryCallable($operation);
		$this->atom_query->run();
	}
}