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
     * @var ilAtomQuery|null
     */
    protected $atom_query;

    /**
     * @var bool
     */
    private $assessmentLogEnabled = false;

    /**
     * @param ilDBInterface $db
     */
    public function __construct(ilDBInterface $db)
    {
        $this->db = $db;
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
        $tables = $this->getTablesUsedDuringSolutionUpdate();

        if ($this->isAssessmentLogEnabled()) {
            $tables = array_merge($tables, $this->getTablesUsedDuringAssessmentLog());
        }

        $this->atom_query = $this->db->buildAtomQuery();
        foreach ($tables as $table) {
            $this->atom_query->addTableLock($table['name'])->lockSequence((bool) $table['sequence']);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function onBeforeExecutingUserQuestionResultUpdateOperation()
    {
        $this->atom_query = $this->db->buildAtomQuery();
        foreach ($this->getTablesUsedDuringResultUpdate() as $table) {
            $this->atom_query->addTableLock($table['name'])->lockSequence((bool) $table['sequence']);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function onBeforeExecutingUserSolutionAdoptOperation()
    {
        $this->atom_query = $this->db->buildAtomQuery();
        foreach (array_merge(
            $this->getTablesUsedDuringSolutionUpdate(),
            $this->getTablesUsedDuringResultUpdate()
        ) as $table) {
            $this->atom_query->addTableLock($table['name'])->lockSequence((bool) $table['sequence']);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function onBeforeExecutingUserTestResultUpdateOperation()
    {
        $this->atom_query = $this->db->buildAtomQuery();
        $this->atom_query->addTableLock('tst_result_cache');
        $this->atom_query->addTableLock('tst_test_result')->lockSequence(true);
        $this->atom_query->addTableLock('tst_solutions')->lockSequence(true);
    }

    /**
     * {@inheritdoc}
     */
    protected function executeOperation(callable $operation)
    {
        if ($this->atom_query) {
            $this->atom_query->addQueryCallable(function (ilDBInterface $ilDB) use ($operation) {
                $operation();
            });
            $this->atom_query->run();
        } else {
            $operation();
        }

        $this->atom_query = null;
    }
}
