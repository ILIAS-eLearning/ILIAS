<?php

declare(strict_types=1);

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
 * Class ilAtomQueryLock
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 *
 *         Implements Atom-Queries with Table Locks, currently used in all other implementations than Galera
 */
class ilAtomQueryLock extends ilAtomQueryBase implements ilAtomQuery
{
    /**
     * @var string[]
     */
    protected array $locked_table_full_names = [];
    /**
     * @var string[]
     */
    protected array $locked_table_names = [];


    /**
     * Fire your Queries
     *
     * @throws \ilAtomQueryException
     */
    public function run(): void
    {
        $this->checkBeforeRun();
        $this->runWithLocks();
    }


    /**
     * @throws \ilAtomQueryException
     */
    protected function runWithLocks(): void
    {
        $this->ilDBInstance->lockTables($this->getLocksForDBInstance());
        try {
            $this->runQueries();
        } catch (Exception $e) {
            $this->ilDBInstance->unlockTables();
            throw $e;
        }
        $this->ilDBInstance->unlockTables();
    }


    /**
     * @throws \ilAtomQueryException
     * @return array<int, array<string, int|string|bool>>
     */
    protected function getLocksForDBInstance(): array
    {
        $locks = array();
        foreach ($this->tables as $table) {
            $full_name = $table->getTableName() . $table->getAlias();
            if (in_array($full_name, $this->locked_table_full_names, true)) {
                throw new ilAtomQueryException('', ilAtomQueryException::DB_ATOM_IDENTICAL_TABLES);
            }
            $this->locked_table_full_names[] = $full_name;

            if (!in_array($table->getTableName(), $this->locked_table_names, true)) {
                $locks[] = array( 'name' => $table->getTableName(), 'type' => $table->getLockLevel() );
                $this->locked_table_names[] = $table->getTableName();
                if ($table->isLockSequence() && $this->ilDBInstance->sequenceExists($table->getTableName())) {
                    $locks[] = array( 'name' => $table->getTableName(), 'type' => $table->getLockLevel(), 'sequence' => true );
                }
            }
            if ($table->getAlias() !== '') {
                $locks[] = array( 'name' => $table->getTableName(), 'type' => $table->getLockLevel(), 'alias' => $table->getAlias() );
            }
        }

        return $locks;
    }
}
