<?php
require_once('exception.ilDatabaseException.php');

/**
 * Class ilAtomQueryException
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilAtomQueryException extends ilDatabaseException
{
    const DB_ATOM_GENERAL = 10900;
    const DB_ATOM_LOCK_TABLE_NONEXISTING = 10901;
    const DB_ATOM_LOCK_WRONG_LEVEL = 10902;
    const DB_ATOM_CLOSURE_WRONG_FORMAT = 10903;
    const DB_ATOM_ISO_WRONG_LEVEL = 10904;
    const DB_ATOM_ANO_NOT_AVAILABLE = 10905;
    const DB_ATOM_LOCK_NO_TABLE = 10906;
    const DB_ATOM_CLOSURE_NONE = 10907;
    const DB_ATOM_CLOSURE_ALREADY_SET = 10908;
    const DB_ATOM_IDENTICAL_TABLES = 10909;


    /**
     * @param $code
     * @return string
     */
    protected function tranlateException($code)
    {
        $message = 'An undefined Exception occured';
        switch ($code) {
            case static::DB_ATOM_GENERAL:
                $message = 'An undefined exception in ilAtomQuery has occured';
                break;
            case static::DB_ATOM_LOCK_TABLE_NONEXISTING:
                $message = 'Table locks only work with existing tables';
                break;
            case static::DB_ATOM_LOCK_WRONG_LEVEL:
                $message = 'The current Isolation-level does not support the desired lock-level. use ilAtomQuery::LOCK_READ or ilAtomQuery::LOCK_WRITE';
                break;
            case static::DB_ATOM_CLOSURE_WRONG_FORMAT:
                $message = 'Please provide a Closure with your database-actions by adding with ilAtomQuery->addQueryClosure(function($ilDB) use ($my_vars) { $ilDB->doStuff(); });';
                break;
            case static::DB_ATOM_ISO_WRONG_LEVEL:
                $message = 'This isolation-level is currently unsupported';
                break;
            case static::DB_ATOM_ANO_NOT_AVAILABLE:
                $message = 'Anomaly not available';
                break;
            case static::DB_ATOM_LOCK_NO_TABLE:
                $message = 'ilAtomQuery needs at least one table to be locked';
                break;
            case static::DB_ATOM_CLOSURE_NONE:
                $message = 'There is no Closure available';
                break;
            case static::DB_ATOM_CLOSURE_ALREADY_SET:
                $message = 'Only one Closure per ilAtomQuery is possible';
                break;
            case static::DB_ATOM_IDENTICAL_TABLES:
                $message = 'A Table and/or alias-name can only be locked once';
                break;
        }

        return 'ilAtomQuery: ' . $message . '. ';
    }
}
