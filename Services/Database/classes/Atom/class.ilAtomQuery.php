<?php
require_once('./Services/Database/exceptions/exception.ilDatabaseException.php');

/**
 * Class ilAtomQuery
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
	 * @var callable
	 */
	protected $query;
	/**
	 * @var Closure[]
	 */
	protected $queries = array();
	/**
	 * @var
	 */
	protected $ilDBInstance;
	/**
	 * @var ilAtomQuery
	 */
	protected static $instance;


	/**
	 * ilAtomQuery constructor.
	 *
	 * @param \ilDBInterface $ilDBInstance
	 * @param int $isolation_level
	 */
	public function __construct(ilDBInterface $ilDBInstance, $isolation_level = self::ISOLATION_SERIALIZABLE) {
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
	 * @param $table_name
	 * @param $lock_level
	 * @throws \ilDatabaseException
	 */
	public function addTable($table_name, $lock_level) {
		if (!in_array($lock_level, array( static::LOCK_READ, static::LOCK_WRITE ))) {
			throw new ilDatabaseException('The current Isolation-level does not support the desired lock-level');
		}
		$this->tables[] = array( $table_name, $lock_level );
	}


	/**
	 * @param \Closure $query
	 */
	public function addQueryClosure(Closure $query) {
		$this->queries[] = $query;
	}


	public function run() {
		self::checkIsolationLevel($this->getIsolationLevel());
		$this->checkQueries();

		if ($has_write_locks && $this->getIsolationLevel() != self::ISOLATION_SERIALIZABLE) {
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
	 * @param int $isolation_level
	 */
	public function setIsolationLevel($isolation_level) {
		$this->isolation_level = $isolation_level;
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
			if (!$query instanceof Closure) {
				throw new ilDatabaseException('Please provide a Closure with your database-actions by adding with ilAtomQuery->addQueryClosure(function($ilDB) use ($my_vars) { $ilDB->doStuff(); });');
			}
		}
	}


	protected function runQueries() {
		foreach ($this->queries as $query) {
			/**
			 * @var $query Closure
			 */
			$query($this->ilDBInstance);
		}
	}


	protected function runWithTransactions() {
		$e = null;
		$i = 0;
		do {
			try {
				$this->ilDBInstance->beginTransaction();
				$this->runQueries();
				$this->ilDBInstance->commit();
			} catch (ilDatabaseException $e) {
				if ($i > 10) {
					throw $e;
				}
			}
			$i ++;
		} while ($e instanceof ilDatabaseException);
	}


	protected function runWithLocks() {
		$this->ilDBInstance->lockTables($this->getLocksForDBInstance());
		$this->runQueries();
		$this->ilDBInstance->unlockTables();
	}
}
