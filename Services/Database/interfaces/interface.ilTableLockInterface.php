<?php

/**
 * Class ilTableLockInterface
 *
 * Defines methods, which a Table-Lock used in ilAtomQuery provides.
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilTableLockInterface
{

    /**
     * Set true/false whether you would like to lock an existing sequence-table, too
     * Without lockSequence(true) sequences are not locked
     *
     * @param bool $lock_bool
     * @return ilTableLockInterface
     */
    public function lockSequence($lock_bool);


    /**
     * If you use Alias' in your Queries which have to be locked by ilAtomQuery, "LOCK TABLE" needs to lock both of the original table and the
     * alias-table. Provide the name of your alias here
     *
     * @param string $alias_name
     * @return ilTableLockInterface
     */
    public function aliasName($alias_name);
}
