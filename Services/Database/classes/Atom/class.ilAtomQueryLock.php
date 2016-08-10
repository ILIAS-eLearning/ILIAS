<?php
require_once('./Services/Database/interfaces/interface.ilAtomQuery.php');

/**
 * Class ilAtomQueryLock
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 *
 *         Implements Atom-Queries with Table Locks, currently used in all other implementations than Galera
 */
class ilAtomQueryLock extends ilAtomQueryBase implements ilAtomQuery {

	/**
	 * Fire your Queries
	 *
	 * @throws \ilAtomQueryException
	 */
	public function run() {
		$this->checkBeforeRun();
		$this->runWithLocks();
	}


	/**
	 * @throws \ilAtomQueryException
	 */
	protected function runWithLocks() {
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
	 */
	protected function getLocksForDBInstance() {
		$locks = array();
		foreach ($this->tables as $table) {
			$locks[] = array( 'name' => $table->getTableName(), 'type' => $table->getLockLevel() );
			if ($table->getAlias()) {
				$locks[] = array( 'name' => $table->getTableName(), 'type' => $table->getLockLevel(), 'alias' => $table->getAlias() );
			}
			if ($table->isLockSequence() && $this->ilDBInstance->sequenceExists($table->getTableName())) {
				$locks[] = array( 'name' => $this->ilDBInstance->getSequenceName($table->getTableName()), 'type' => $table->getLockLevel() );
			}
		}

		return $locks;
	}
}
