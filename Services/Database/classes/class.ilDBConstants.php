<?php declare(strict_types=1);

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
    const TYPE_MYSQLI = 'mysqli';
    // Development identifiers (will be removed in 5.3), are mapped with Main and Experimental types
    const TYPE_PDO_MYSQL_INNODB = 'pdo-mysql-innodb';
    const TYPE_PDO_MYSQL_GALERA = 'pdo-mysql-galera';
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
    // Characters
    const MYSQL_CHARACTER_UTF8 = 'utf8';
    const MYSQL_CHARACTER_UTF8MB4 = 'utf8mb4';
    // Collations
    const MYSQL_COLLATION_UTF8 = 'utf8_general_ci';
    const MYSQL_COLLATION_UTF8MB4 = 'utf8mb4_general_ci';
    const MYSQL_COLLATION_UTF8_CZECH = "utf8_czech_ci";
    const MYSQL_COLLATION_UTF8_DANISH = "utf8_danish_ci";
    const MYSQL_COLLATION_UTF8_ESTONIAN = "utf8_estonian_ci";
    const MYSQL_COLLATION_UTF8_ICELANDIC = "utf8_icelandic_ci";
    const MYSQL_COLLATION_UTF8_LATVIAN = "utf8_latvian_ci";
    const MYSQL_COLLATION_UTF8_LITHUANIAN = "utf8_lithuanian_ci";
    const MYSQL_COLLATION_UTF8_PERSIAN = "utf8_persian_ci";
    const MYSQL_COLLATION_UTF8_POLISH = "utf8_polish_ci";
    const MYSQL_COLLATION_UTF8_ROMAN = "utf8_roman_ci";
    const MYSQL_COLLATION_UTF8_ROMANIAN = "utf8_romanian_ci";
    const MYSQL_COLLATION_UTF8_SLOVAK = "utf8_slovak_ci";
    const MYSQL_COLLATION_UTF8_SLOVENIAN = "utf8_slovenian_ci";
    const MYSQL_COLLATION_UTF8_SPANISH2 = "utf8_spanish2_ci";
    const MYSQL_COLLATION_UTF8_SPANISH = "utf8_spanish_ci";
    const MYSQL_COLLATION_UTF8_SWEDISH = "utf8_swedish_ci";
    const MYSQL_COLLATION_UTF8_TURKISH = "utf8_turkish_ci";

    // Mapping AutoExec
    const AUTOQUERY_INSERT = 1;
    const AUTOQUERY_UPDATE = 2;
    const AUTOQUERY_DELETE = 3;
    const AUTOQUERY_SELECT = 4;
    const PREPARE_MANIP = false;
    // Other
    const MB4_REPLACEMENT = "?";
    /**
     * @var string[]
     */
    protected static array $descriptions = array(
        // Main
        ilDBConstants::TYPE_MYSQL => "MySQL 5.7.x or higher with InnoDB-Engine",
        ilDBConstants::TYPE_MYSQLI => "MySQL 5.7.x or higher with InnoDB-Engine",
        ilDBConstants::TYPE_INNODB => "MySQL 5.7.x or higher with InnoDB-Engine",
        ilDBConstants::TYPE_GALERA => "Galera-Cluster (experimental)",
        // Development identifiers (will be removed in 5.3)
        ilDBConstants::TYPE_PDO_MYSQL_GALERA => "Galera-Cluster (experimental) [developers-identifier]",
    );


    /**
     * @return string[]
     */
    public static function getInstallableTypes() : array
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

    public static function getAvailableTypes(bool $with_descriptions = true) : array
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


    public static function getSupportedTypes() : array
    {
        return array(
            ilDBConstants::TYPE_MYSQL,
            ilDBConstants::TYPE_INNODB,
            ilDBConstants::TYPE_GALERA,
        );
    }

    /**
     * @return string[]
     */
    public static function getAvailableCollations() : array
    {
        return [
            ilDBConstants::MYSQL_COLLATION_UTF8,
            ilDBConstants::MYSQL_COLLATION_UTF8MB4,
            ilDBConstants::MYSQL_COLLATION_UTF8_CZECH,
            ilDBConstants::MYSQL_COLLATION_UTF8_DANISH,
            ilDBConstants::MYSQL_COLLATION_UTF8_ESTONIAN,
            ilDBConstants::MYSQL_COLLATION_UTF8_ICELANDIC,
            ilDBConstants::MYSQL_COLLATION_UTF8_LATVIAN,
            ilDBConstants::MYSQL_COLLATION_UTF8_LITHUANIAN,
            ilDBConstants::MYSQL_COLLATION_UTF8_PERSIAN,
            ilDBConstants::MYSQL_COLLATION_UTF8_POLISH,
            ilDBConstants::MYSQL_COLLATION_UTF8_ROMAN,
            ilDBConstants::MYSQL_COLLATION_UTF8_ROMANIAN,
            ilDBConstants::MYSQL_COLLATION_UTF8_SLOVAK,
            ilDBConstants::MYSQL_COLLATION_UTF8_SLOVENIAN,
            ilDBConstants::MYSQL_COLLATION_UTF8_SPANISH2,
            ilDBConstants::MYSQL_COLLATION_UTF8_SPANISH,
            ilDBConstants::MYSQL_COLLATION_UTF8_SWEDISH,
            ilDBConstants::MYSQL_COLLATION_UTF8_TURKISH
        ];
    }

    public static function describe(string $type) : string
    {
        return self::$descriptions[$type];
    }
}
