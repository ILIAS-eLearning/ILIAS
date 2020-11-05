<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Lock;

/**
 * Interface LockHandler
 * @package ILIAS\ResourceStorage
 */
class LockHandlerilDB implements LockHandler
{
    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * LockHandlerilDB constructor.
     * @param \ilDBInterface $db
     */
    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function lockTables(array $table_names, callable $during) : LockHandlerResult
    {
        $lock = $this->db->buildAtomQuery();
        foreach ($table_names as $table_name) {
            $lock->addTableLock($table_name);
        }
        $lock->addQueryCallable(static function (\ilDBInterface $db) use ($during) {
            $during();
        });

        return new LockHandlerResultilDB($lock);
    }
}
