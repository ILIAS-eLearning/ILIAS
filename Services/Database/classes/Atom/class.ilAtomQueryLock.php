<?php
require_once('./Services/Database/interfaces/interface.ilAtomQuery.php');

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
     * @var array
     */
    protected $locked_table_full_names = array();
    /**
     * @var array
     */
    protected $locked_table_names = array();


    /**
     * Fire your Queries
     *
     * @throws \ilAtomQueryException
     */
    public function run()
    {
        $this->checkBeforeRun();
        $this->runWithLocks();
    }


    /**
     * @throws \ilAtomQueryException
     */
    protected function runWithLocks()
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
     * @return array
     * @throws \ilAtomQueryException
     */
    protected function getLocksForDBInstance()
    {
        $locks = array();
        foreach ($this->tables as $table) {
            $full_name = $table->getTableName() . $table->getAlias();
            if (in_array($full_name, $this->locked_table_full_names)) {
                throw new ilAtomQueryException('', ilAtomQueryException::DB_ATOM_IDENTICAL_TABLES);
            }
            $this->locked_table_full_names[] = $full_name;

            if (!in_array($table->getTableName(), $this->locked_table_names)) {
                $locks[] = array( 'name' => $table->getTableName(), 'type' => $table->getLockLevel() );
                $this->locked_table_names[] = $table->getTableName();
                if ($table->isLockSequence() && $this->ilDBInstance->sequenceExists($table->getTableName())) {
                    $locks[] = array( 'name' => $table->getTableName(), 'type' => $table->getLockLevel(), 'sequence' => true );
                }
            }
            if ($table->getAlias()) {
                $locks[] = array( 'name' => $table->getTableName(), 'type' => $table->getLockLevel(), 'alias' => $table->getAlias() );
            }
        }

        return $locks;
    }
}
