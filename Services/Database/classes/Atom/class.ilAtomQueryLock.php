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
			$table_name = $table[0];
			$lock_level = $table[1];
			$lock_sequence_too = $table[2];
			$locks[] = array( 'name' => $table_name, 'type' => $lock_level );
			if ($lock_sequence_too && $this->ilDBInstance->sequenceExists($table_name)) {
				$locks[] = array( 'name' => $this->ilDBInstance->getSequenceName($table_name), 'type' => $lock_level );
			}
		}

		return $locks;
	}
}
