<?php
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
     * @param $a_type
     *
     * @return ilDBInterface
     * @throws ilDatabaseException
     */
    public static function getWrapper($a_type)
    {
        global $DIC;
        $ilClientIniFile = null;
        if ($DIC != null && $DIC->offsetExists('ilClientIniFile')) {
            /**
             * @var $ilClientIniFile ilIniFile
             */
            $ilClientIniFile = $DIC['ilClientIniFile'];
        }

        if ($a_type == "" && $ilClientIniFile instanceof ilIniFile) {
            $a_type = $ilClientIniFile->readVariable("db", "type");
        }
        if ($a_type == "") {
            $a_type = ilDBConstants::TYPE_INNODB;
        }

        switch ($a_type) {
            case ilDBConstants::TYPE_POSTGRES:
            case ilDBConstants::TYPE_PDO_POSTGRE:
                $ilDB = new ilDBPdoPostgreSQL();
                break;
            case ilDBConstants::TYPE_PDO_MYSQL_INNODB:
            case ilDBConstants::TYPE_INNODB:
                $ilDB = new ilDBPdoMySQLInnoDB();
                break;
            case ilDBConstants::TYPE_PDO_MYSQL_MYISAM:
            case ilDBConstants::TYPE_MYSQL:
                $ilDB = new ilDBPdoMySQLMyISAM();
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
