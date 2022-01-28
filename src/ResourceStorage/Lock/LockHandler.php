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
interface LockHandler
{
    public function lockTables(array $table_names, callable $during) : LockHandlerResult;
}
