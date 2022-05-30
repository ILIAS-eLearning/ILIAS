<?php declare(strict_types=1);

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
 * Class ilAtomQuery
 * Use ilAtomQuery to fire Database-Actions which have to be done without beeing influenced by other queries or which can influence other queries as
 * well. Depending on the current Database-engine, this can be done by using transaction or with table-locks
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class ilAtomQueryBase
{
    protected const ITERATIONS = 10;
    /**
     * @var int[]
     */
    protected static array $available_isolations_levels = array(
        ilAtomQuery::ISOLATION_READ_UNCOMMITED,
        ilAtomQuery::ISOLATION_READ_COMMITED,
        ilAtomQuery::ISOLATION_REPEATED_READ,
        ilAtomQuery::ISOLATION_SERIALIZABLE,
    );
    /**
     * @var int[]
     */
    protected static array $possible_anomalies = array(
        ilAtomQuery::ANO_LOST_UPDATES,
        ilAtomQuery::ANO_DIRTY_READ,
        ilAtomQuery::ANO_NON_REPEATED_READ,
        ilAtomQuery::ANO_PHANTOM,
    );
    /**
     * @var int[][]
     */
    protected static array $anomalies_map = array(
        ilAtomQuery::ISOLATION_READ_UNCOMMITED => array(
            ilAtomQuery::ANO_LOST_UPDATES,
            ilAtomQuery::ANO_DIRTY_READ,
            ilAtomQuery::ANO_NON_REPEATED_READ,
            ilAtomQuery::ANO_PHANTOM,
        ),
        ilAtomQuery::ISOLATION_READ_COMMITED => array(
            ilAtomQuery::ANO_NON_REPEATED_READ,
            ilAtomQuery::ANO_PHANTOM,
        ),
        ilAtomQuery::ISOLATION_REPEATED_READ => array(
            ilAtomQuery::ANO_PHANTOM,
        ),
        ilAtomQuery::ISOLATION_SERIALIZABLE => array(),
    );
    protected int $isolation_level = ilAtomQuery::ISOLATION_SERIALIZABLE;
    /**
     * @var ilTableLock[]
     */
    protected array $tables = array();
    /**
     * @var callable
     */
    protected $query;
    protected \ilDBInterface $ilDBInstance;

    /**
     * ilAtomQuery constructor.
     * @param int $isolation_level currently only ISOLATION_SERIALIZABLE is available
     */
    public function __construct(ilDBInterface $ilDBInstance, int $isolation_level = ilAtomQuery::ISOLATION_SERIALIZABLE)
    {
        static::checkIsolationLevel($isolation_level);
        $this->ilDBInstance = $ilDBInstance;
        $this->isolation_level = $isolation_level;
    }

    //
    //
    //
    /**
     * @return int[]
     */
    public function getRisks() : array
    {
        return static::getPossibleAnomalies($this->getIsolationLevel());
    }

    /**
     * Add table-names which are influenced by your queries, MyISAm has to lock those tables.
     * You get an ilTableLockInterface with further possibilities, e.g.:
     * $ilAtomQuery->addTableLock('my_table')->lockSequence(true)->aliasName('my_alias');
     * the lock-level is determined by ilAtomQuery
     */
    public function addTableLock(string $table_name) : ilTableLockInterface
    {
        $ilTableLock = new ilTableLock($table_name, $this->ilDBInstance);
        $ilTableLock->setLockLevel($this->getDeterminedLockLevel());
        $this->tables[] = $ilTableLock;

        return $ilTableLock;
    }

    protected function getDeterminedLockLevel() : int
    {
        return ilAtomQuery::LOCK_WRITE;
    }

    /**
     * All action on the database during this isolation has to be passed as Callable to ilAtomQuery.
     * An example (Closure):
     * $ilAtomQuery->addQueryClosure( function (ilDBInterface $ilDB) use ($new_obj_id, $current_id) {
     *        $ilDB->doStuff();
     *    });
     * An example (Callable Class):
     * class ilMyAtomQueryClass {
     *      public function __invoke(ilDBInterface $ilDB) {
     *          $ilDB->doStuff();
     *      }
     * }
     * $ilAtomQuery->addQueryClosure(new ilMyAtomQueryClass());
     * @throws ilAtomQueryException
     */
    public function addQueryCallable(callable $query) : void
    {
        if ($this->query) {
            throw new ilAtomQueryException('', ilAtomQueryException::DB_ATOM_CLOSURE_ALREADY_SET);
        }
        if (!$this->checkCallable($query)) {
            throw new ilAtomQueryException('', ilAtomQueryException::DB_ATOM_CLOSURE_WRONG_FORMAT);
        }
        $this->query = $query;
    }

    /**
     * @throws \ilAtomQueryException
     */
    public function replaceQueryCallable(callable $query) : void
    {
        if (!$this->checkCallable($query)) {
            throw new ilAtomQueryException('', ilAtomQueryException::DB_ATOM_CLOSURE_WRONG_FORMAT);
        }
        $this->query = $query;
    }

    /**
     * Fire your Queries
     * @throws \ilAtomQueryException
     */
    abstract public function run() : void;

    public function getIsolationLevel() : int
    {
        return $this->isolation_level;
    }

    /**
     * @throws \ilAtomQueryException
     */
    public static function isThereRiskThat(int $isolation_level, int $anomaly) : bool
    {
        static::checkIsolationLevel($isolation_level);
        static::checkAnomaly($anomaly);

        return in_array($anomaly, static::getPossibleAnomalies($isolation_level));
    }

    /**
     * @return int[]
     */
    public static function getPossibleAnomalies(int $isolation_level) : array
    {
        static::checkIsolationLevel($isolation_level);

        return self::$anomalies_map[$isolation_level];
    }

    /**
     * @throws \ilAtomQueryException
     */
    public static function checkIsolationLevel(int $isolation_level) : void
    {
        // The following Isolations are currently not supported
        if (in_array($isolation_level, array(
            ilAtomQuery::ISOLATION_READ_UNCOMMITED,
            ilAtomQuery::ISOLATION_READ_COMMITED,
            ilAtomQuery::ISOLATION_REPEATED_READ,
        ))) {
            throw new ilAtomQueryException('Level: ' . $isolation_level, ilAtomQueryException::DB_ATOM_ISO_WRONG_LEVEL);
        }
        // Check if a available Isolation level is selected
        if (!in_array($isolation_level, self::$available_isolations_levels)) {
            throw new ilAtomQueryException('Level: ' . $isolation_level, ilAtomQueryException::DB_ATOM_ISO_WRONG_LEVEL);
        }
    }

    /**
     * @throws \ilAtomQueryException
     */
    public static function checkAnomaly(int $anomaly) : void
    {
        if (!in_array($anomaly, self::$possible_anomalies)) {
            throw new ilAtomQueryException('Anomaly: ' . $anomaly, ilAtomQueryException::DB_ATOM_ANO_NOT_AVAILABLE);
        }
    }

    /**
     * @throws \ilAtomQueryException
     */
    protected function checkQueries() : void
    {
        if (!($this->query instanceof \Traversable) && (is_array($this->query) && 0 === count($this->query))) {
            throw new ilAtomQueryException('', ilAtomQueryException::DB_ATOM_CLOSURE_NONE);
        }

        foreach ($this->query as $query) {
            if (!$this->checkCallable($query)) {
                throw new ilAtomQueryException('', ilAtomQueryException::DB_ATOM_CLOSURE_WRONG_FORMAT);
            }
        }
    }

    public function checkCallable(callable $query) : bool
    {
        if (!is_callable($query)) {
            return false; // Won't be triggered sidn type-hinting already checks this
        }
        if (is_array($query)) {
            return false;
        }
        if (is_string($query)) {
            return false;
        }
        
        $is_a_closure = ($query instanceof Closure);
        if (!$is_a_closure) {
            $ref = new ReflectionClass($query);
            foreach ($ref->getMethods() as $method) {
                if ($method->getName() === '__invoke') {
                    return true;
                }
            }

            return false;
        }
        if ($is_a_closure) {
            $ref = new ReflectionFunction($query);
            $parameters = $ref->getParameters();
            if (count($parameters) !== 1) {
                return false;
            }
            $reflectionClass = $parameters[0]->getType();
            return $reflectionClass && $reflectionClass->getName() === ilDBInterface::class;
        }

        return true;
    }

    protected function hasWriteLocks() : bool
    {
        $has_write_locks = false;
        foreach ($this->tables as $table) {
            if ($table->getLockLevel() === ilAtomQuery::LOCK_WRITE) {
                $has_write_locks = true;
            }
        }

        return $has_write_locks;
    }

    /**
     * @throws ilAtomQueryException
     */
    protected function runQueries() : void
    {
        $query = $this->query;
        $query($this->ilDBInstance);
    }

    /**
     * @throws \ilAtomQueryException
     */
    protected function checkBeforeRun() : void
    {
        $this->checkQueries();

        if ($this->hasWriteLocks() && $this->getIsolationLevel() != ilAtomQuery::ISOLATION_SERIALIZABLE) {
            throw new ilAtomQueryException('', ilAtomQueryException::DB_ATOM_LOCK_WRONG_LEVEL);
        }

        if (count($this->tables) === 0) {
            throw new ilAtomQueryException('', ilAtomQueryException::DB_ATOM_LOCK_NO_TABLE);
        }
    }
}
