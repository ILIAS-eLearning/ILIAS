<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Lock;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Interface LockHandler
 * @package ILIAS\ResourceStorage
 */
class LockHandlerilDB implements LockHandler
{
    protected \ilDBInterface $db;

    /**
     * LockHandlerilDB constructor.
     * @internal
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
        $lock->addQueryCallable(static function (\ilDBInterface $db) use ($during) : void {
            $during();
        });

        return new LockHandlerResultilDB($lock);
    }
}
