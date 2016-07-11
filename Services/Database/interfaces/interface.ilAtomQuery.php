<?php

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
	 * Add table-names which are influenced by your queries, MyISAm has to lock those tables. Lock
	 *
	 * @param string $table_name
	 * @param int $lock_level use ilAtomQuery::LOCK_READ or ilAtomQuery::LOCK_WRITE
	 * @param bool $lock_sequence_too
	 * @throws \ilDatabaseException
	 */
	public function addTable($table_name, $lock_level, $lock_sequence_too = false);


	/**
	 * @param $table_name
	 * @param bool $lock_sequence_too
	 */
	public function lockTableWrite($table_name, $lock_sequence_too = false);


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
	 */
	public function addQueryCallable(Callable $query);


	/**
	 * Fire your Queries
	 *
	 * @throws \ilDatabaseException
	 */
	public function run();


	/**
	 * @param $isolation_level
	 * @throws \ilDatabaseException
	 */
	public static function checkIsolationLevel($isolation_level);


	/**
	 * @return int
	 */
	public function getIsolationLevel();
}
