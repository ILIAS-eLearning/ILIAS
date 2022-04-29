<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilDBWrapperFactory
 *
 * DB Wrapper Factory. Delivers a DB wrapper object depending on given
 * DB type and DSN.
 *
 * @author  Alex Killing <alex.killing@gmx.de>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 *
 *
 * @ingroup ServicesDatabase
 */
class ilDBWrapperFactory
{

    /**
     * @param string $a_type
     * @return ilDBPdoInterface
     * @throws ilDatabaseException
     */
    public static function getWrapper(string $a_type) : \ilDBPdoInterface
    {
        switch ($a_type) {
            case ilDBConstants::TYPE_POSTGRES:
            case ilDBConstants::TYPE_PDO_POSTGRE:
                $ilDB = new ilDBPdoPostgreSQL();
                break;
            case ilDBConstants::TYPE_PDO_MYSQL_INNODB:
            case ilDBConstants::TYPE_INNODB:
                $ilDB = new ilDBPdoMySQLInnoDB();
                break;
            case ilDBConstants::TYPE_GALERA:
                $ilDB = new ilDBPdoMySQLGalera();
                break;
            default:
                throw new ilDatabaseException("No viable database-type given: " . var_export($a_type, true));
        }

        return $ilDB;
    }
}
