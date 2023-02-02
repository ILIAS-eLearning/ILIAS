<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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

    public function isAssessmentLogEnabled(): bool
    {
        return $this->assessmentLogEnabled;
    }

    public function setAssessmentLogEnabled($assessmentLogEnabled): void
    {
        $this->assessmentLogEnabled = $assessmentLogEnabled;
    }

    /**
     * @return array
     */
    private function getTablesUsedDuringAssessmentLog(): array
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
    private function getTablesUsedDuringSolutionUpdate(): array
    {
        return array(
            array('name' => 'tst_solutions', 'sequence' => true)
        );
    }

    /**
     * @return array
     */
    private function getTablesUsedDuringResultUpdate(): array
    {
        return array(
            array('name' => 'tst_test_result', 'sequence' => true)
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function onBeforeExecutingUserSolutionUpdateOperation(): void
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
    protected function onBeforeExecutingUserQuestionResultUpdateOperation(): void
    {
        $this->atom_query = $this->db->buildAtomQuery();
        foreach ($this->getTablesUsedDuringResultUpdate() as $table) {
            $this->atom_query->addTableLock($table['name'])->lockSequence((bool) $table['sequence']);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function onBeforeExecutingUserSolutionAdoptOperation(): void
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
    protected function onBeforeExecutingUserTestResultUpdateOperation(): void
    {
        $this->atom_query = $this->db->buildAtomQuery();
        $this->atom_query->addTableLock('tst_result_cache');
        $this->atom_query->addTableLock('tst_test_result')->lockSequence(true);
        $this->atom_query->addTableLock('tst_solutions')->lockSequence(true);
    }

    /**
     * {@inheritdoc}
     */
    protected function executeOperation(callable $operation): void
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
