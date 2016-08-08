<?php
require_once('./Services/Database/exceptions/exception.ilAtomQueryException.php');

/**
 * Interface ilAtomQuery
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilAtomQuery {

	// Lock levels
	const LOCK_WRITE = 1;
	const LOCK_READ = 2;
	// Isolation-Levels
	const ISOLATION_READ_UNCOMMITED = 1;
	const ISOLATION_READ_COMMITED = 2;
	const ISOLATION_REPEATED_READ = 3;
	const ISOLATION_SERIALIZABLE = 4;
	// Anomalies
	const ANO_LOST_UPDATES = 1;
	const ANO_DIRTY_READ = 2;
	const ANO_NON_REPEATED_READ = 3;
	const ANO_PHANTOM = 4;


	/**
	 * Add table-names which are influenced by your queries, MyISAm has to lock those tables.
	 *
	 * the lock-level is determined by ilAtomQuery
	 *
	 * @param string $table_name
	 * @param bool $lock_sequence_too
	 * @throws \ilAtomQueryException
	 */
	public function lockTable($table_name, $lock_sequence_too = false);


	/**
	 * Every action on the database during this isolation has to be passed as Callable to ilAtomQuery.
	 * An example (Closure):
	 * $ilAtomQuery->addQueryClosure( function (ilDBInterface $ilDB) use ($new_obj_id, $current_id) {
	 *        $ilDB->doStuff();
	 *    });
	 *
	 *
	 * An example (Callable Class):
	 * class ilMyAtomQueryClass {
	 *      public function __invoke(ilDBInterface $ilDB) {
	 *          $ilDB->doStuff();
	 *      }
	 * }
	 *
	 * $ilAtomQuery->addQueryClosure(new ilMyAtomQueryClass());
	 *
	 * @param \Callable $query
	 * @throws ilAtomQueryException
	 */
	public function addQueryCallable(callable $query);


	/**
	 * Every action on the database during this isolation has to be passed as Callable to ilAtomQuery.
	 * An example (Closure):
	 * $ilAtomQuery->addQueryClosure( function (ilDBInterface $ilDB) use ($new_obj_id, $current_id) {
	 *        $ilDB->doStuff();
	 *    });
	 *
	 *
	 * An example (Callable Class):
	 * class ilMyAtomQueryClass {
	 *      public function __invoke(ilDBInterface $ilDB) {
	 *          $ilDB->doStuff();
	 *      }
	 * }
	 *
	 * $ilAtomQuery->addQueryClosure(new ilMyAtomQueryClass());
	 *
	 * @param \Callable $query
	 * @throws ilAtomQueryException
	 */
	public function replaceQueryCallable(callable $query);


	/**
	 * Fire your Queries
	 *
	 * @throws \ilAtomQueryException
	 */
	public function run();


	/**
	 * @param $isolation_level
	 * @throws \ilAtomQueryException
	 */
	public static function checkIsolationLevel($isolation_level);


	/**
	 * @return int
	 */
	public function getIsolationLevel();


	/**
	 * @param callable $query
	 * @return bool
	 */
	public function checkCallable(callable $query);
}
