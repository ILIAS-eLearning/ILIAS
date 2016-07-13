<?php
require_once('./Services/Database/exceptions/exception.ilDatabaseException.php');
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
	 * @throws \ilDatabaseException
	 */
	public function run() {
		$this->checkBeforeRun();
		$this->runWithLocks();
	}


	/**
	 * @throws ilDatabaseException
	 */
	protected function runWithLocks() {
		if ($this->tables) {
			$this->ilDBInstance->lockTables($this->getLocksForDBInstance());
		}
		$this->runQueries();
		if ($this->tables) {
			$this->ilDBInstance->unlockTables();
		}
	}
}
