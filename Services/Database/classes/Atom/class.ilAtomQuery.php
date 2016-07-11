<?php
require_once('./Services/Database/exceptions/exception.ilDatabaseException.php');

/**
 * Class ilAtomQuery
 *
 * Use ilAtomQuery to fire Database-Actions which have to be done without beeing influenced by other queries or which can influence other queries as
 * well. Depending on the current Database-engine, this can be done by using transaction or with table-locks
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilAtomQuery {

	// Lock levels
	const LOCK_WRITE = 1;
	const LOCK_READ = 2;
	// Isolation-Levels
	const ISOLATION_READ_UNCOMMITED = 1;
	const ISOLATION_READ_COMMITED = 2;
	const ISOLATION_REPEATED_READ = 3;
	const ISOLATION_SERIALIZABLE = 4;
	const ITERATIONS = 10;
	/**
	 * @var array
	 */
	protected static $available_isolations_levels = array(
		self::ISOLATION_READ_UNCOMMITED,
		self::ISOLATION_READ_COMMITED,
		self::ISOLATION_REPEATED_READ,
		self::ISOLATION_SERIALIZABLE,
	);
	// Anomalies
	const ANO_LOST_UPDATES = 1;
	const ANO_DIRTY_READ = 2;
	const ANO_NON_REPEATED_READ = 3;
	const ANO_PHANTOM = 4;
	/**
	 * @var array
	 */
	protected static $possible_anomalies = array(
		self::ANO_LOST_UPDATES,
		self::ANO_DIRTY_READ,
		self::ANO_NON_REPEATED_READ,
		self::ANO_PHANTOM,
	);
	/**
	 * @var array
	 */
	protected static $anomalies_map = array(
		self::ISOLATION_READ_UNCOMMITED => array(
			self::ANO_LOST_UPDATES,
			self::ANO_DIRTY_READ,
			self::ANO_NON_REPEATED_READ,
			self::ANO_PHANTOM,
		),
		self::ISOLATION_READ_COMMITED   => array(
			self::ANO_NON_REPEATED_READ,
			self::ANO_PHANTOM,
		),
		self::ISOLATION_REPEATED_READ   => array(
			self::ANO_PHANTOM,
		),
		self::ISOLATION_SERIALIZABLE    => array(),
	);
	/**
	 * @var int
	 */
	protected $isolation_level = self::ISOLATION_SERIALIZABLE;
	/**
	 * @var array
	 */
	protected $tables = array();
	/**
	 * @var Callable[]
	 */
	protected $queries = array();
	/**
	 * @var int
	 */
	protected $running_query = 0;


	/**
	 * ilAtomQuery constructor.
	 *
	 * @param \ilDBInterface $ilDBInstance
	 * @param int $isolation_level currently only ISOLATION_SERIALIZABLE is available
	 */
	public function __construct(ilDBInterface $ilDBInstance, $isolation_level = self::ISOLATION_SERIALIZABLE) {
		static::checkIsolationLevel($isolation_level);
		$this->ilDBInstance = $ilDBInstance;
		$this->isolation_level = $isolation_level;
	}

	//
	//
	//
	/**
	 * @return array
	 */
	public function getRisks() {
		return static::getPossibleAnomalies($this->getIsolationLevel());
	}


	/**
	 * Add table-names which are influenced by your queries, MyISAm has to lock those tables. Lock
	 *
	 * @param string $table_name
	 * @param int $lock_level use ilAtomQuery::LOCK_READ or ilAtomQuery::LOCK_WRITE
	 * @param bool $lock_sequence_too
	 * @throws \ilDatabaseException
	 */
	public function addTable($table_name, $lock_level, $lock_sequence_too = false) {
		if(!$table_name || !$this->ilDBInstance->tableExists($table_name)) {
			throw new ilDatabaseException('Table locks only work with existing tables');
		}
		if (!in_array($lock_level, array( static::LOCK_READ, static::LOCK_WRITE ))) {
			throw new ilDatabaseException('The current Isolation-level does not support the desired lock-level. use ilAtomQuery::LOCK_READ or ilAtomQuery::LOCK_WRITE');
		}
		$this->tables[] = array( $table_name, $lock_level, $lock_sequence_too );
	}


	/**
	 * @param $table_name
	 * @param bool $lock_sequence_too
	 */
	public function lockTableWrite($table_name, $lock_sequence_too = false) {
		$this->tables[] = array( $table_name, self::LOCK_WRITE, $lock_sequence_too );
	}


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
	public function addQueryCallable(Callable $query) {
		$this->queries[] = $query;
	}


	/**
	 * Fire your Queries
	 *
	 * @throws \ilDatabaseException
	 */
	public function run() {
		$this->checkQueries();

		if ($this->hasWriteLocks() && $this->getIsolationLevel() != self::ISOLATION_SERIALIZABLE) {
			throw new ilDatabaseException('The selected Isolation-level is not allowd when locking tables with write-locks');
		}

		if ($this->ilDBInstance->supportsTransactions()) {
			$this->runWithTransactions();
		} else {
			$this->runWithLocks();
		}
	}
	//
	//
	//
	/**
	 * @return int
	 */
	public function getIsolationLevel() {
		return $this->isolation_level;
	}


	/**
	 * @param $isolation_level
	 * @param $anomaly
	 * @return bool
	 * @throws \ilDatabaseException
	 */
	public static function isThereRiskThat($isolation_level, $anomaly) {
		static::checkIsolationLevel($isolation_level);
		static::checkAnomaly($anomaly);

		return in_array($anomaly, static::getPossibleAnomalies($isolation_level));
	}


	/**
	 * @param $isolation_level
	 * @return array
	 */
	public static function getPossibleAnomalies($isolation_level) {
		static::checkIsolationLevel($isolation_level);

		return self::$anomalies_map[$isolation_level];
	}


	/**
	 * @param $isolation_level
	 * @throws \ilDatabaseException
	 */
	public static function checkIsolationLevel($isolation_level) {
		// The following Isolations are currently not supported
		if (in_array($isolation_level, array( self::ISOLATION_READ_UNCOMMITED, self::ISOLATION_READ_COMMITED, self::ISOLATION_REPEATED_READ ))) {
			throw new ilDatabaseException('This isolation-level is currently unsupported');
		}
		// Check if a available Isolation level is selected
		if (!in_array($isolation_level, self::$available_isolations_levels)) {
			throw new ilDatabaseException('Isolation-Level not available');
		}
	}


	/**
	 * @param $anomalie
	 * @throws \ilDatabaseException
	 */
	public static function checkAnomaly($anomalie) {
		if (!in_array($anomalie, self::$available_isolations_levels)) {
			throw new ilDatabaseException('Isolation-Level not available');
		}
	}


	/**
	 * @throws \ilDatabaseException
	 */
	protected function checkQueries() {
		foreach ($this->queries as $query) {
			if (!is_callable($query)) {
				throw new ilDatabaseException('Please provide a Closure with your database-actions by adding with ilAtomQuery->addQueryClosure(function($ilDB) use ($my_vars) { $ilDB->doStuff(); });');
			}
		}
	}


	/**
	 * @return bool
	 */
	protected function hasWriteLocks() {
		$has_write_locks = false;
		foreach ($this->tables as $table) {
			$lock_level = $table[1];
			if ($lock_level == self::LOCK_WRITE) {
				$has_write_locks = true;
			}
		}

		return $has_write_locks;
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


	/**
	 * @throws ilDatabaseException
	 */
	protected function runQueries() {
		foreach ($this->queries as $i => $query) {
			if ($i < $this->running_query) {
				continue;
			}
			/**
			 * @var $query Callable
			 */
			$query($this->ilDBInstance);

			$this->running_query ++;
		}
	}


	/**
	 * @throws \ilDatabaseException
	 */
	protected function runWithTransactions() {
		$i = 0;
		do {
			$e = null;
			try {
				$this->ilDBInstance->beginTransaction();
				$this->runQueries();
				$this->ilDBInstance->commit();
			} catch (ilDatabaseException $e) {
				$this->ilDBInstance->rollback();
				if ($i >= self::ITERATIONS - 1) {
					throw $e;
				}
			}
			$i ++;
		} while ($e instanceof ilDatabaseException);
	}


	/**
	 * @throws ilDatabaseException
	 */
	protected function runWithLocks() {
		$this->ilDBInstance->lockTables($this->getLocksForDBInstance());
		$this->runQueries();
		$this->ilDBInstance->unlockTables();
	}
}
