<?php
require_once('./Services/Database/classes/PDO/FieldDefinition/class.ilDBPdoFieldDefinition.php');
require_once('./Services/Database/classes/Atom/class.ilAtomQueryBase.php');

/**
 * Class ilDBConstants
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDBConstants
{
    const FETCHMODE_ASSOC = 2;
    const FETCHMODE_OBJECT = 3;
    const FETCHMODE_DEFAULT = self::FETCHMODE_ASSOC;
    // Main Types
    const TYPE_INNODB = 'innodb';
    const TYPE_MYSQL = 'mysql';
    // Experimental
    const TYPE_GALERA = 'galera';
    const TYPE_POSTGRES = 'postgres';
    const TYPE_MYSQLI = 'mysqli';
    // Development identifiers (will be removed in 5.3), are mapped with Main and Experimental types
    const TYPE_PDO_MYSQL_INNODB = 'pdo-mysql-innodb';
    const TYPE_PDO_MYSQL_MYISAM = 'pdo-mysql-myisam';
    const TYPE_PDO_MYSQL_GALERA = 'pdo-mysql-galera';
    const TYPE_PDO_POSTGRE = 'pdo-postgre';
    // Locks
    const LOCK_WRITE = ilAtomQuery::LOCK_WRITE;
    const LOCK_READ = ilAtomQuery::LOCK_READ;
    // Modules
    const MODULE_MANAGER = 'Manager';
    const MODULE_REVERSE = 'Reverse';
    // Formats
    const INDEX_FORMAT = ilDBPdoFieldDefinition::INDEX_FORMAT;
    const SEQUENCE_FORMAT = ilDBPdoFieldDefinition::SEQUENCE_FORMAT;
    const SEQUENCE_COLUMNS_NAME = ilDBPdoFieldDefinition::SEQUENCE_COLUMNS_NAME;
    // Types
    const T_CLOB = ilDBPdoFieldDefinition::T_CLOB;
    const T_DATE = ilDBPdoFieldDefinition::T_DATE;
    const T_DATETIME = ilDBPdoFieldDefinition::T_DATETIME;
    const T_FLOAT = ilDBPdoFieldDefinition::T_FLOAT;
    const T_INTEGER = ilDBPdoFieldDefinition::T_INTEGER;
    const T_TEXT = ilDBPdoFieldDefinition::T_TEXT;
    const T_TIME = ilDBPdoFieldDefinition::T_TIME;
    const T_TIMESTAMP = ilDBPdoFieldDefinition::T_TIMESTAMP;
    const T_BLOB = ilDBPdoFieldDefinition::T_BLOB;
    // Engines
    const MYSQL_ENGINE_INNODB = 'InnoDB';
    const MYSQL_ENGINE_MYISAM = 'MyISAM';
    // Characters
    const MYSQL_CHARACTER_UTF8 = 'utf8';
    const MYSQL_CHARACTER_UTF8MB4 = 'utf8mb4';
    // Collations
    const MYSQL_COLLATION_UTF8 = 'utf8_general_ci';
    const MYSQL_COLLATION_UTF8MB4 = 'utf8mb4_general_ci';
    // Mapping AutoExec
    const AUTOQUERY_INSERT = 1;
    const AUTOQUERY_UPDATE = 2;
    const AUTOQUERY_DELETE = 3;
    const AUTOQUERY_SELECT = 4;
    const PREPARE_MANIP = false;
    // Other
    const MB4_REPLACEMENT = "?";
    /**
     * @var array
     */
    protected static $descriptions = array(
        // Main
        ilDBConstants::TYPE_MYSQL => "MySQL 5.5.x or higher (MyISAM engine)",
        ilDBConstants::TYPE_MYSQLI => "MySQL 5.5.x or higher (MyISAM engine)",
        ilDBConstants::TYPE_INNODB => "MySQL 5.5.x or higher (InnoDB engine)",
        // Experimental
        ilDBConstants::TYPE_POSTGRES => "Postgres (experimental)",
        ilDBConstants::TYPE_GALERA => "Galera-Cluster (experimental)",
        // Development identifiers (will be removed in 5.3)
        ilDBConstants::TYPE_PDO_MYSQL_MYISAM => "MySQL 5.5.x or higher (MyISAM engine) [developers-identifier]",
        ilDBConstants::TYPE_PDO_MYSQL_INNODB => "MySQL 5.5.x or higher (InnoDB engine) [developers-identifier]",
        ilDBConstants::TYPE_PDO_POSTGRE => "Postgres (experimental) [developers-identifier]",
        ilDBConstants::TYPE_PDO_MYSQL_GALERA => "Galera-Cluster (experimental) [developers-identifier]",
    );


    /**
     * @return array
     */
    public static function getInstallableTypes()
    {
        return array(
            // Main
            ilDBConstants::TYPE_MYSQL,
            ilDBConstants::TYPE_INNODB,
            // Experimental
            ilDBConstants::TYPE_GALERA,
            ilDBConstants::TYPE_GALERA,
        );
    }


    /**
     * @param bool $with_descriptions
     * @return array
     */
    public static function getAvailableTypes($with_descriptions = true)
    {
        $types = self::getSupportedTypes();
        if ($with_descriptions) {
            $return = array();
            foreach ($types as $type) {
                $return [$type] = self::describe($type);
            }
            $types = $return;
        }

        return $types;
    }


    /**
     * @return array
     */
    public static function getSupportedTypes()
    {
        return array(
            ilDBConstants::TYPE_MYSQL,
            ilDBConstants::TYPE_INNODB,
            ilDBConstants::TYPE_POSTGRES,
            ilDBConstants::TYPE_GALERA,
        );
    }


    /**
     * @param $type
     * @return string
     */
    public static function describe($type)
    {
        return self::$descriptions[$type];
    }
}
