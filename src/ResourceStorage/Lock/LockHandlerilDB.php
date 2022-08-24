<?php

declare(strict_types=1);

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
 *********************************************************************/

namespace ILIAS\ResourceStorage\Lock;

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

    public function lockTables(array $table_names, callable $during): LockHandlerResult
    {
        $lock = $this->db->buildAtomQuery();
        foreach ($table_names as $table_name) {
            $lock->addTableLock($table_name);
        }
        $lock->addQueryCallable(static function (\ilDBInterface $db) use ($during): void {
            $during();
        });

        return new LockHandlerResultilDB($lock);
    }
}
