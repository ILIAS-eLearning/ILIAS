<?php declare(strict_types=1);

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
    public function lockSequence(bool $lock_bool) : ilTableLockInterface;

    /**
     * If you use Alias' in your Queries which have to be locked by ilAtomQuery, "LOCK TABLE" needs to lock both of the original table and the
     * alias-table. Provide the name of your alias here
     */
    public function aliasName(string $alias_name) : ilTableLockInterface;
}
