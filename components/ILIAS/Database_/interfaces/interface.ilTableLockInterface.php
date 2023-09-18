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
 *
 *********************************************************************/

/**
 * Class ilTableLockInterface
 * Defines methods, which a Table-Lock used in ilAtomQuery provides.
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilTableLockInterface
{
    /**
     * Set true/false whether you would like to lock an existing sequence-table, too
     * Without lockSequence(true) sequences are not locked
     */
    public function lockSequence(bool $lock_bool): ilTableLockInterface;

    /**
     * If you use Alias' in your Queries which have to be locked by ilAtomQuery, "LOCK TABLE" needs to lock both of the original table and the
     * alias-table. Provide the name of your alias here
     */
    public function aliasName(string $alias_name): ilTableLockInterface;
}
