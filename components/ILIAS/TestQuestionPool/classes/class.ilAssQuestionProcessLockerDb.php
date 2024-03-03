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
 * @package components\ILIAS/Test
 */
class ilAssQuestionProcessLockerDb extends ilAssQuestionProcessLocker
{
    protected ?ilAtomQuery $atom_query = null;

    public function __construct(private ilDBInterface $db)
    {
        $this->db = $db;
    }

    private function getTablesUsedDuringSolutionUpdate(): array
    {
        return [
            ['name' => 'tst_solutions', 'sequence' => true],
            ['name' => PassPresentedVariablesRepo::TABLE_NAME, 'sequence' => false]
        ];
    }

    private function getTablesUsedDuringResultUpdate(): array
    {
        return array(
            array('name' => 'tst_test_result', 'sequence' => true)
        );
    }

    protected function onBeforeExecutingUserSolutionUpdateOperation(): void
    {
        $tables = $this->getTablesUsedDuringSolutionUpdate();

        $this->atom_query = $this->db->buildAtomQuery();
        foreach ($tables as $table) {
            $this->atom_query->addTableLock($table['name'])->lockSequence((bool) $table['sequence']);
        }
    }

    protected function onBeforeExecutingUserQuestionResultUpdateOperation(): void
    {
        $this->atom_query = $this->db->buildAtomQuery();
        foreach ($this->getTablesUsedDuringResultUpdate() as $table) {
            $this->atom_query->addTableLock($table['name'])->lockSequence((bool) $table['sequence']);
        }
    }

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

    protected function onBeforeExecutingUserTestResultUpdateOperation(): void
    {
        $this->atom_query = $this->db->buildAtomQuery();
        $this->atom_query->addTableLock('tst_result_cache');
        $this->atom_query->addTableLock('tst_test_result')->lockSequence(true);
        $this->atom_query->addTableLock('tst_solutions')->lockSequence(true);
    }

    protected function executeOperation(callable $operation): void
    {
        if ($this->atom_query !== null) {
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
