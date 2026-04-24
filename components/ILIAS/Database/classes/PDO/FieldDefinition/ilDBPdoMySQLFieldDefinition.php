<?php

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

declare(strict_types=1);

use ILIAS\Database\FieldDefinition;

/**
 * Class ilDBPdoMySQLFieldDefinition
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDBPdoMySQLFieldDefinition implements FieldDefinition
{
    public array $lobs;
    public const DEFAULT_DECIMAL_PLACES = 2;
    public const DEFAULT_TEXT_LENGTH = 4000;
    public const DEFINITION_COLUMN_NAME = "/^[a-z]+[_a-z0-9]*$/";
    public const DEFINITION_TABLE_NAME = "/^[a-z]+[_a-z0-9]*$/";

    private const MAX_TABLE_IDENTIFIER_LENGTH = 63;

    protected static self $instance;
    /**
     * @var string[][]
     */
    public array $allowed_attributes_old = [
        self::T_TEXT => ['length', 'notnull', 'default', 'fixed'],
        self::T_INTEGER => ['length', 'notnull', 'default', 'unsigned'],
        self::T_FLOAT => ['notnull', 'default'],
        self::T_DATE => ['notnull', 'default'],
        self::T_TIME => ['notnull', 'default'],
        self::T_TIMESTAMP => ['notnull', 'default'],
        self::T_CLOB => ['notnull', 'default'],
        self::T_BLOB => ['notnull', 'default'],
    ];
    /**
     * @var string[][]
     */
    public array $allowed_attributes = [
        "text" => ["length", "notnull", "default", "fixed"],
        "integer" => ["length", "notnull", "default", "unsigned"],
        "float" => ["notnull", "default"],
        "date" => ["notnull", "default"],
        "time" => ["notnull", "default"],
        "timestamp" => ["notnull", "default"],
        "clob" => ["length", "notnull", "default"],
        "blob" => ["length", "notnull", "default"],
    ];
    protected array $max_length = [
        self::T_INTEGER => [1, 2, 3, 4, 8],
        self::T_TEXT => 4000,
    ];
    /**
     * @var string[]
     */
    protected array $available_types = [
        self::T_TEXT,
        self::T_INTEGER,
        self::T_FLOAT,
        self::T_DATE,
        self::T_TIME,
        self::T_TIMESTAMP,
        self::T_CLOB,
        self::T_BLOB,
    ];
    /**
     * @var string[]
     */
    protected array $reserved_mysql = [
        "ACCESSIBLE",
        "ACCOUNT",
        "ACTION",
        "ADD",
        "AFTER",
        "AGAINST",
        "AGGREGATE",
        "ALGORITHM",
        "ALL",
        "ALTER",
        "ALWAYS",
        "ANALYSE",
        "ANALYZE",
        "AND",
        "ANY",
        "AS",
        "ASC",
        "ASCII",
        "ASENSITIVE",
        "AT",
        "AUTHORS",
        "AUTOEXTEND_SIZE",
        "AUTO_INCREMENT",
        "AVG",
        "AVG_ROW_LENGTH",
        "BACKUP",
        "BEFORE",
        "BEGIN",
        "BETWEEN",
        "BIGINT",
        "BINARY",
        "BINLOG",
        "BIT",
        "BLOB",
        "BLOCK",
        "BOOL",
        "BOOLEAN",
        "BOTH",
        "BTREE",
        "BY",
        "BYTE",
        "CACHE",
        "CALL",
        "CASCADE",
        "CASCADED",
        "CASE",
        "CATALOG_NAME",
        "CHAIN",
        "CHANGE",
        "CHANGED",
        "CHANNEL",
        "CHAR",
        "CHARACTER",
        "CHARSET",
        "CHECK",
        "CHECKSUM",
        "CIPHER",
        "CLASS_ORIGIN",
        "CLIENT",
        "CLOSE",
        "COALESCE",
        "CODE",
        "COLLATE",
        "COLLATION",
        "COLUMN",
        "COLUMNS",
        "COLUMN_FORMAT",
        "COLUMN_NAME",
        "COMMENT",
        "COMMIT",
        "COMMITTED",
        "COMPACT",
        "COMPLETION",
        "COMPRESSED",
        "COMPRESSION",
        "CONCURRENT",
        "CONDITION",
        "CONNECTION",
        "CONSISTENT",
        "CONSTRAINT",
        "CONSTRAINT_CATALOG",
        "CONSTRAINT_NAME",
        "CONSTRAINT_SCHEMA",
        "CONTAINS",
        "CONTEXT",
        "CONTINUE",
        "CONTRIBUTORS",
        "CONVERT",
        "CPU",
        "CREATE",
        "CROSS",
        "CUBE",
        "CURRENT",
        "CURRENT_DATE",
        "CURRENT_TIME",
        "CURRENT_TIMESTAMP",
        "CURRENT_USER",
        "CURSOR",
        "CURSOR_NAME",
        "DATA",
        "DATABASE",
        "DATABASES",
        "DATAFILE",
        "DATE",
        "DATETIME",
        "DAY",
        "DAY_HOUR",
        "DAY_MICROSECOND",
        "DAY_MINUTE",
        "DAY_SECOND",
        "DEALLOCATE",
        "DEC",
        "DECIMAL",
        "DECLARE",
        "DEFAULT",
        "DEFAULT_AUTH",
        "DEFINER",
        "DELAYED",
        "DELAY_KEY_WRITE",
        "DELETE",
        "DESC",
        "DESCRIBE",
        "DES_KEY_FILE",
        "DETERMINISTIC",
        "DIAGNOSTICS",
        "DIRECTORY",
        "DISABLE",
        "DISCARD",
        "DISK",
        "DISTINCT",
        "DISTINCTROW",
        "DIV",
        "DO",
        "DOUBLE",
        "DROP",
        "DUAL",
        "DUMPFILE",
        "DUPLICATE",
        "DYNAMIC",
        "EACH",
        "ELSE",
        "ELSEIF",
        "ENABLE",
        "ENCLOSED",
        "ENCRYPTION",
        "END",
        "ENDS",
        "ENGINE",
        "ENGINES",
        "ENUM",
        "ERROR",
        "ERRORS",
        "ESCAPE",
        "ESCAPED",
        "EVENT",
        "EVENTS",
        "EVERY",
        "EXCHANGE",
        "EXECUTE",
        "EXISTS",
        "EXIT",
        "EXPANSION",
        "EXPIRE",
        "EXPLAIN",
        "EXPORT",
        "EXTENDED",
        "EXTENT_SIZE",
        "FALSE",
        "FAST",
        "FAULTS",
        "FETCH",
        "FIELDS",
        "FILE",
        "FILE_BLOCK_SIZE",
        "FILTER",
        "FIRST",
        "FIXED",
        "FLOAT",
        "FLOAT4",
        "FLOAT8",
        "FLUSH",
        "FOLLOWS",
        "FOR",
        "FORCE",
        "FOREIGN",
        "FORMAT",
        "FOUND",
        "FROM",
        "FULL",
        "FULLTEXT",
        "FUNCTION",
        "GENERAL",
        "GENERATED",
        "GEOMETRY",
        "GEOMETRYCOLLECTION",
        "GET",
        "GET_FORMAT",
        "GLOBAL",
        "GRANT",
        "GRANTS",
        "GROUP",
        "GROUP_REPLICATION",
        "HANDLER",
        "HASH",
        "HAVING",
        "HELP",
        "HIGH_PRIORITY",
        "HOST",
        "HOSTS",
        "HOUR",
        "HOUR_MICROSECOND",
        "HOUR_MINUTE",
        "HOUR_SECOND",
        "IDENTIFIED",
        "IF",
        "IGNORE",
        "IGNORE_SERVER_IDS",
        "IMPORT",
        "IN",
        "INDEX",
        "INDEXES",
        "INFILE",
        "INITIAL_SIZE",
        "INNER",
        "INOUT",
        "INSENSITIVE",
        "INSERT",
        "INSERT_METHOD",
        "INSTALL",
        "INSTANCE",
        "INT",
        "INT1",
        "INT2",
        "INT3",
        "INT4",
        "INT8",
        "INTEGER",
        "INTERVAL",
        "INTO",
        "INVOKER",
        "IO",
        "IO_AFTER_GTIDS",
        "IO_BEFORE_GTIDS",
        "IO_THREAD",
        "IPC",
        "IS",
        "ISOLATION",
        "ISSUER",
        "ITERATE",
        "JOIN",
        "JSON",
        "KEY",
        "KEYS",
        "KEY_BLOCK_SIZE",
        "KILL",
        "LANGUAGE",
        "LAST",
        "LEADING",
        "LEAVE",
        "LEAVES",
        "LEFT",
        "LESS",
        "LEVEL",
        "LIKE",
        "LIMIT",
        "LINEAR",
        "LINES",
        "LINESTRING",
        "LIST",
        "LOAD",
        "LOCAL",
        "LOCALTIME",
        "LOCALTIMESTAMP",
        "LOCK",
        "LOCKS",
        "LOGFILE",
        "LOGS",
        "LONG",
        "LONGBLOB",
        "LONGTEXT",
        "LOOP",
        "LOW_PRIORITY",
        "MASTER",
        "MASTER_AUTO_POSITION",
        "MASTER_BIND",
        "MASTER_CONNECT_RETRY",
        "MASTER_DELAY",
        "MASTER_HEARTBEAT_PERIOD",
        "MASTER_HOST",
        "MASTER_LOG_FILE",
        "MASTER_LOG_POS",
        "MASTER_PASSWORD",
        "MASTER_PORT",
        "MASTER_RETRY_COUNT",
        "MASTER_SERVER_ID",
        "MASTER_SSL",
        "MASTER_SSL_CA",
        "MASTER_SSL_CAPATH",
        "MASTER_SSL_CERT",
        "MASTER_SSL_CIPHER",
        "MASTER_SSL_CRL",
        "MASTER_SSL_CRLPATH",
        "MASTER_SSL_KEY",
        "MASTER_SSL_VERIFY_SERVER_CERT",
        "MASTER_TLS_VERSION",
        "MASTER_USER",
        "MATCH",
        "MAXVALUE",
        "MAX_CONNECTIONS_PER_HOUR",
        "MAX_QUERIES_PER_HOUR",
        "MAX_ROWS",
        "MAX_SIZE",
        "MAX_STATEMENT_TIME",
        "MAX_UPDATES_PER_HOUR",
        "MAX_USER_CONNECTIONS",
        "MEDIUM",
        "MEDIUMBLOB",
        "MEDIUMINT",
        "MEDIUMTEXT",
        "MEMORY",
        "MERGE",
        "MESSAGE_TEXT",
        "MICROSECOND",
        "MIDDLEINT",
        "MIGRATE",
        "MINUTE",
        "MINUTE_MICROSECOND",
        "MINUTE_SECOND",
        "MIN_ROWS",
        "MOD",
        "MODE",
        "MODIFIES",
        "MODIFY",
        "MONTH",
        "MULTILINESTRING",
        "MULTIPOINT",
        "MULTIPOLYGON",
        "MUTEX",
        "MYSQL_ERRNO",
        "NAME",
        "NAMES",
        "NATIONAL",
        "NATURAL",
        "NCHAR",
        "NDB",
        "NDBCLUSTER",
        "NEVER",
        "NEW",
        "NEXT",
        "NO",
        "NODEGROUP",
        "NONBLOCKING",
        "NONE",
        "NOT",
        "NO_WAIT",
        "NO_WRITE_TO_BINLOG",
        "NULL",
        "NUMBER",
        "NUMERIC",
        "NVARCHAR",
        "OFFSET",
        "OLD_PASSWORD",
        "ON",
        "ONE",
        "ONE_SHOT",
        "ONLY",
        "OPEN",
        "OPTIMIZE",
        "OPTIMIZER_COSTS",
        "OPTION",
        "OPTIONALLY",
        "OPTIONS",
        "OR",
        "ORDER",
        "OUT",
        "OUTER",
        "OUTFILE",
        "OWNER",
        "PACK_KEYS",
        "PAGE",
        "PARSER",
        "PARSE_GCOL_EXPR",
        "PARTIAL",
        "PARTITION",
        "PARTITIONING",
        "PARTITIONS",
        "PASSWORD",
        "PHASE",
        "PLUGIN",
        "PLUGINS",
        "PLUGIN_DIR",
        "POINT",
        "POLYGON",
        "PORT",
        "PRECEDES",
        "PRECISION",
        "PREPARE",
        "PRESERVE",
        "PREV",
        "PRIMARY",
        "PRIVILEGES",
        "PROCEDURE",
        "PROCESSLIST",
        "PROFILE",
        "PROFILES",
        "PROXY",
        "PURGE",
        "QUARTER",
        "QUERY",
        "QUICK",
        "RANGE",
        "READ",
        "READS",
        "READ_ONLY",
        "READ_WRITE",
        "REAL",
        "REBUILD",
        "RECOVER",
        "REDOFILE",
        "REDO_BUFFER_SIZE",
        "REDUNDANT",
        "REFERENCES",
        "REGEXP",
        "RELAY",
        "RELAYLOG",
        "RELAY_LOG_FILE",
        "RELAY_LOG_POS",
        "RELAY_THREAD",
        "RELEASE",
        "RELOAD",
        "REMOVE",
        "RENAME",
        "REORGANIZE",
        "REPAIR",
        "REPEAT",
        "REPEATABLE",
        "REPLACE",
        "REPLICATE_DO_DB",
        "REPLICATE_DO_TABLE",
        "REPLICATE_IGNORE_DB",
        "REPLICATE_IGNORE_TABLE",
        "REPLICATE_REWRITE_DB",
        "REPLICATE_WILD_DO_TABLE",
        "REPLICATE_WILD_IGNORE_TABLE",
        "REPLICATION",
        "REQUIRE",
        "RESET",
        "RESIGNAL",
        "RESTORE",
        "RESTRICT",
        "RESUME",
        "RETURN",
        "RETURNED_SQLSTATE",
        "RETURNS",
        "REVERSE",
        "REVOKE",
        "RIGHT",
        "RLIKE",
        "ROLLBACK",
        "ROLLUP",
        "ROTATE",
        "ROUTINE",
        "ROW",
        "ROWS",
        "ROW_COUNT",
        "ROW_FORMAT",
        "RTREE",
        "SAVEPOINT",
        "SCHEDULE",
        "SCHEMA",
        "SCHEMAS",
        "SCHEMA_NAME",
        "SECOND",
        "SECOND_MICROSECOND",
        "SECURITY",
        "SELECT",
        "SENSITIVE",
        "SEPARATOR",
        "SERIAL",
        "SERIALIZABLE",
        "SERVER",
        "SESSION",
        "SET",
        "SHARE",
        "SHOW",
        "SHUTDOWN",
        "SIGNAL",
        "SIGNED",
        "SIMPLE",
        "SLAVE",
        "SLOW",
        "SMALLINT",
        "SNAPSHOT",
        "SOCKET",
        "SOME",
        "SONAME",
        "SOUNDS",
        "SOURCE",
        "SPATIAL",
        "SPECIFIC",
        "SQL",
        "SQLEXCEPTION",
        "SQLSTATE",
        "SQLWARNING",
        "SQL_AFTER_GTIDS",
        "SQL_AFTER_MTS_GAPS",
        "SQL_BEFORE_GTIDS",
        "SQL_BIG_RESULT",
        "SQL_BUFFER_RESULT",
        "SQL_CACHE",
        "SQL_CALC_FOUND_ROWS",
        "SQL_NO_CACHE",
        "SQL_SMALL_RESULT",
        "SQL_THREAD",
        "SQL_TSI_DAY",
        "SQL_TSI_HOUR",
        "SQL_TSI_MINUTE",
        "SQL_TSI_MONTH",
        "SQL_TSI_QUARTER",
        "SQL_TSI_SECOND",
        "SQL_TSI_WEEK",
        "SQL_TSI_YEAR",
        "SSL",
        "STACKED",
        "START",
        "STARTING",
        "STARTS",
        "STATS_AUTO_RECALC",
        "STATS_PERSISTENT",
        "STATS_SAMPLE_PAGES",
        "STATUS",
        "STOP",
        "STORAGE",
        "STORED",
        "STRAIGHT_JOIN",
        "STRING",
        "SUBCLASS_ORIGIN",
        "SUBJECT",
        "SUBPARTITION",
        "SUBPARTITIONS",
        "SUPER",
        "SUSPEND",
        "SWAPS",
        "SWITCHES",
        "TABLE",
        "TABLES",
        "TABLESPACE",
        "TABLE_CHECKSUM",
        "TABLE_NAME",
        "TEMPORARY",
        "TEMPTABLE",
        "TERMINATED",
        "TEXT",
        "THAN",
        "THEN",
        "TIME",
        "TIMESTAMP",
        "TIMESTAMPADD",
        "TIMESTAMPDIFF",
        "TINYBLOB",
        "TINYINT",
        "TINYTEXT",
        "TO",
        "TRAILING",
        "TRANSACTION",
        "TRIGGER",
        "TRIGGERS",
        "TRUE",
        "TRUNCATE",
        "TYPE",
        "TYPES",
        "UNCOMMITTED",
        "UNDEFINED",
        "UNDO",
        "UNDOFILE",
        "UNDO_BUFFER_SIZE",
        "UNICODE",
        "UNINSTALL",
        "UNION",
        "UNIQUE",
        "UNKNOWN",
        "UNLOCK",
        "UNSIGNED",
        "UNTIL",
        "UPDATE",
        "UPGRADE",
        "USAGE",
        "USE",
        "USER",
        "USER_RESOURCES",
        "USE_FRM",
        "USING",
        "UTC_DATE",
        "UTC_TIME",
        "UTC_TIMESTAMP",
        "VALIDATION",
        "VALUE",
        "VALUES",
        "VARBINARY",
        "VARCHAR",
        "VARCHARACTER",
        "VARIABLES",
        "VARYING",
        "VIEW",
        "VIRTUAL",
        "WAIT",
        "WARNINGS",
        "WEEK",
        "WEIGHT_STRING",
        "WHEN",
        "WHERE",
        "WHILE",
        "WITH",
        "WITHOUT",
        "WORK",
        "WRAPPER",
        "WRITE",
        "X509",
        "XA",
        "XID",
        "XML",
        "XOR",
        "YEAR",
        "YEAR_MONTH",
        "ZEROFILL",
    ];
    /**
     * @var string[]
     */
    protected array $reserved_postgres = [
        "ALL",
        "ANALYSE",
        "ANALYZE",
        "AND",
        "ANY",
        "ARRAY",
        "AS",
        "ASC",
        "ASYMMETRIC",
        "AUTHORIZATION",
        "BETWEEN",
        "BINARY",
        "BOTH",
        "CASE",
        "CAST",
        "CHECK",
        "COLLATE",
        "COLUMN",
        "CONSTRAINT",
        "CREATE",
        "CROSS",
        "CURRENT_DATE",
        "CURRENT_ROLE",
        "CURRENT_TIME",
        "CURRENT_TIMESTAMP",
        "CURRENT_USER",
        "DEFAULT",
        "DEFERRABLE",
        "DESC",
        "DISTINCT",
        "DO",
        "ELSE",
        "END",
        "EXCEPT",
        "FALSE",
        "FOR",
        "FOREIGN",
        "FREEZE",
        "FROM",
        "FULL",
        "GRANT",
        "GROUP",
        "HAVING",
        "ILIKE",
        "IN",
        "INITIALLY",
        "INNER",
        "INTERSECT",
        "INTO",
        "IS",
        "ISNULL",
        "JOIN",
        "LEADING",
        "LEFT",
        "LIKE",
        "LIMIT",
        "LOCALTIME",
        "LOCALTIMESTAMP",
        "NATURAL",
        "NEW",
        "NOT",
        "NOTNULL",
        "NULL",
        "OFF",
        "OFFSET",
        "OLD",
        "ON",
        "ONLY",
        "OR",
        "ORDER",
        "OUTER",
        "OVERLAPS",
        "PLACING",
        "PRIMARY",
        "REFERENCES",
        "RETURNING",
        "RIGHT",
        "SELECT",
        "SESSION_USER",
        "SIMILAR",
        "SOME",
        "SYMMETRIC",
        "TABLE",
        "THEN",
        "TO",
        "TRAILING",
        "TRUE",
        "UNION",
        "UNIQUE",
        "USER",
        "USING",
        "VERBOSE",
        "WHEN",
        "WHERE",
        "WITH",
    ];

    protected ?\ilMySQLQueryUtils $query_utils = null;

    public function __construct(protected \ilDBInterface $db_instance)
    {
    }

    protected array $valid_default_values = [
        'text' => '',
        'boolean' => true,
        'integer' => 0,
        'decimal' => 0.0,
        'float' => 0.0,
        'timestamp' => '1970-01-01 00:00:00',
        'time' => '00:00:00',
        'date' => '1970-01-01',
        'clob' => '',
        'blob' => '',
    ];

    /**
     * @throws \ilDatabaseException
     */
    public function checkTableName(string $table_name): bool
    {
        if (!preg_match(self::DEFINITION_TABLE_NAME, $table_name)) {
            throw new ilDatabaseException('Table name must only contain _a-z0-9 and must start with a-z.');
        }

        if ($this->isReserved($table_name)) {
            throw new ilDatabaseException("Invalid table name '" . $table_name . "' (Reserved Word).");
        }

        if (stripos($table_name, "sys_") === 0) {
            throw new ilDatabaseException("Invalid table name '" . $table_name . "'. Name must not start with 'sys_'.");
        }

        if (strlen($table_name) > self::MAX_TABLE_IDENTIFIER_LENGTH) {
            throw new ilDatabaseException("Invalid table name '" . $table_name
                . "'. Maximum table identifer length is " . self::MAX_TABLE_IDENTIFIER_LENGTH . " bytes.");
        }

        return true;
    }

    public function isReserved(string $table_name): bool
    {
        return false;
    }

    /**
     * @return string[]
     */
    public function getAllReserved(): array
    {
        return $this->getReservedMysql();
    }

    /**
     * @return string[]
     */
    public function getReservedMysql(): array
    {
        return $this->reserved_mysql;
    }

    /**
     * @param string[] $reserved_mysql
     */
    public function setReservedMysql(array $reserved_mysql): void
    {
        $this->reserved_mysql = $reserved_mysql;
    }


    /**
     * @throws \ilDatabaseException
     */
    public function checkColumnName(string $column_name): bool
    {
        if (!preg_match("/^[a-z]+[_a-z0-9]*$/", $column_name)) {
            throw new ilDatabaseException("Invalid column name '" . $column_name
                . "'. Column name must only contain _a-z0-9 and must start with a-z.");
        }

        if ($this->isReserved($column_name)) {
            throw new ilDatabaseException("Invalid column name '" . $column_name . "' (Reserved Word).");
        }

        if (stripos($column_name, "sys_") === 0) {
            throw new ilDatabaseException("Invalid column name '" . $column_name . "'. Name must not start with 'sys_'.");
        }

        if (strlen($column_name) > 30) {
            throw new ilDatabaseException("Invalid column name '" . $column_name . "'. Maximum column identifer length is 30 bytes.");
        }

        return true;
    }

    /**
     * @throws \ilDatabaseException
     */
    public function checkIndexName(string $a_name): bool
    {
        if (!preg_match("/^[a-z]+[_a-z0-9]*$/", $a_name)) {
            throw new ilDatabaseException("Invalid column name '" . $a_name . "'. Column name must only contain _a-z0-9 and must start with a-z.");
        }

        if ($this->isReserved($a_name)) {
            throw new ilDatabaseException("Invalid column name '" . $a_name . "' (Reserved Word).");
        }

        if (strlen($a_name) > 3) {
            throw new ilDatabaseException("Invalid index name '" . $a_name . "'. Maximum index identifer length is 3 bytes.");
        }

        return true;
    }

    /**
     * @throws \ilDatabaseException
     */
    public function checkColumnDefinition(array $a_def): bool
    {
        // check valid type
        if (!in_array($a_def["type"], $this->getAvailableTypes(), true)) {
            switch ($a_def["type"]) {
                case "boolean":
                    throw new ilDatabaseException("Invalid column type '" . $a_def["type"] . "'. Use integer(1) instead.");

                case "decimal":
                    throw new ilDatabaseException("Invalid column type '" . $a_def["type"] . "'. Use float or integer instead.");

                default:
                    throw new ilDatabaseException("Invalid column type '" . $a_def["type"] . "'. Allowed types are: "
                        . implode(', ', $this->getAvailableTypes()));
            }
        }

        // check used attributes
        $allowed_attributes = $this->getAllowedAttributes();
        foreach (array_keys($a_def) as $k) {
            if ($k !== "type" && !in_array($k, $allowed_attributes[$a_def["type"]], true)) {
                throw new ilDatabaseException("Attribute '" . $k . "' is not allowed for column type '" . $a_def["type"] . "'.");
            }
        }

        // type specific checks
        $max_length = $this->getMaxLength();
        switch ($a_def["type"]) {
            case self::T_TEXT:
                if ((!isset($a_def["length"]) || $a_def["length"] < 1 || $a_def["length"] > $max_length[self::T_TEXT]) && isset($a_def["length"])) {
                    throw new ilDatabaseException("Invalid length '" . $a_def["length"] . "' for type text." . " Length must be >=1 and <= "
                        . $max_length[self::T_TEXT] . ".");
                }
                break;

            case self::T_INTEGER:
                if (isset($a_def["length"]) && !in_array((int) $a_def["length"], $max_length[self::T_INTEGER], true)) {
                    throw new ilDatabaseException("Invalid length '" . $a_def["length"] . "' for type integer." . " Length must be "
                        . implode(', ', $max_length[self::T_INTEGER]) . " (bytes).");
                }
                if ($a_def["unsigned"] ?? null) {
                    throw new ilDatabaseException("Unsigned attribut must not be true for type integer.");
                }
                break;
        }

        return true;
    }

    public function isAllowedAttribute(string $attribute, string $type): bool
    {
        return in_array($attribute, $this->allowed_attributes[$type], true);
    }

    /**
     * @return string[]
     */
    public function getAvailableTypes(): array
    {
        return $this->available_types;
    }

    /**
     * @param string[] $available_types
     */
    public function setAvailableTypes(array $available_types): void
    {
        $this->available_types = $available_types;
    }

    /**
     * @return array<string, string[]>
     */
    public function getAllowedAttributes(): array
    {
        return $this->allowed_attributes;
    }

    /**
     * @param array<string, string[]> $allowed_attributes
     */
    public function setAllowedAttributes(array $allowed_attributes): void
    {
        $this->allowed_attributes = $allowed_attributes;
    }

    public function getMaxLength(): array
    {
        return $this->max_length;
    }

    public function setMaxLength(array $max_length): void
    {
        $this->max_length = $max_length;
    }

    private function getDBInstance(): \ilDBInterface
    {
        return $this->db_instance;
    }

    /**
     * @return string[]
     */
    public function getValidTypes(): array
    {
        $types = $this->valid_default_values;
        $db = $this->getDBInstance();

        if (!empty($db->options['datatype_map'])) {
            foreach ($db->options['datatype_map'] as $type => $mapped_type) {
                if (array_key_exists($mapped_type, $types)) {
                    $types[$type] = $types[$mapped_type];
                } elseif (!empty($db->options['datatype_map_callback'][$type])) {
                    $parameter = ['type' => $type, 'mapped_type' => $mapped_type];
                    $default = call_user_func_array(
                        $db->options['datatype_map_callback'][$type],
                        [&$db, __FUNCTION__, $parameter]
                    );
                    $types[$type] = $default;
                }
            }
        }

        return $types;
    }



    /**
     * @return mixed
     * @throws \ilDatabaseException
     */
    public function getDeclaration(string $type, string $name, array $field)
    {
        $db = $this->getDBInstance();

        if (!empty($db->options['datatype_map'][$type])) {
            $type = $db->options['datatype_map'][$type];
            if (!empty($db->options['datatype_map_callback'][$type])) {
                $parameter = ['type' => $type, 'name' => $name, 'field' => $field];

                return call_user_func_array(
                    $db->options['datatype_map_callback'][$type],
                    [&$db, __FUNCTION__, $parameter]
                );
            }
            $field['type'] = $type;
        }

        if (!array_key_exists($type, $this->getValidTypes())) {
            throw new ilDatabaseException('type not defined: ' . $type);
        }

        $quoted_name = $db->quoteIdentifier($name, true);
        $type_declaration = $this->getTypeDeclaration($field);

        if ($type === 'clob' || $type === 'blob') {
            $notnull = empty($field['notnull']) ? '' : ' NOT NULL';
            return $quoted_name . ' ' . $type_declaration . $notnull;
        }

        return $quoted_name . ' ' . $type_declaration . $this->getDeclarationOptions($field);
    }

    private function getDeclarationOptions(array $field): string
    {
        $charset = empty($field['charset']) ? '' : ' ' . $field['charset'];

        $default = '';
        if (array_key_exists('default', $field)) {
            if ($field['default'] === '') {
                $db = $this->getDBInstance();

                if (empty($field['notnull'])) {
                    $field['default'] = null;
                } else {
                    $valid_default_values = $this->getValidTypes();
                    $field['default'] = $valid_default_values[$field['type']];
                }
                if ($field['default'] === ''
                    && isset($db->options['portability'])
                    && ($db->options['portability'] & 32)
                ) {
                    $field['default'] = ' ';
                }
            }
            $default = ' DEFAULT ' . $this->quote($field['default'], $field['type']);
        } elseif (empty($field['notnull'])) {
            $default = ' DEFAULT NULL';
        }

        $notnull = empty($field['notnull']) ? '' : ' NOT NULL';
        if (isset($field['notnull']) && $field['notnull'] === false) {
            $notnull = ' NULL';
        }

        $collation = empty($field['collation']) ? '' : ' ' . $field['collation'];

        return $charset . $default . $notnull . $collation;
    }

    /**
     * @throws \ilDatabaseException
     *
     * @return array<string, bool>
     */
    public function compareDefinition(array $current, array $previous): array
    {
        $type = empty($current['type']) ? null : $current['type'];

        if (!method_exists($this, "compare{$type}Definition")) {
            $db = $this->getDBInstance();

            if (!empty($db->options['datatype_map_callback'][$type])) {
                $parameter = ['current' => $current, 'previous' => $previous];

                return call_user_func_array(
                    $db->options['datatype_map_callback'][$type],
                    [&$db, __FUNCTION__, $parameter]
                );
            }

            throw new ilDatabaseException('type "' . $current['type'] . '" is not yet supported');
        }

        if (empty($previous['type']) || $previous['type'] != $type) {
            return $current;
        }

        $change = $this->{"compare{$type}Definition"}($current, $previous);

        if ($previous['type'] != $type) {
            $change['type'] = true;
        }

        $previous_notnull = empty($previous['notnull']) ? false : $previous['notnull'];
        $notnull = empty($current['notnull']) ? false : $current['notnull'];
        if ($previous_notnull !== $notnull) {
            $change['notnull'] = true;
        }

        $alt = $previous_notnull ? '' : null;
        $previous_default = array_key_exists(
            'default',
            $previous
        ) ? $previous['default'] : $alt;
        $alt = $notnull ? '' : null;
        $default = array_key_exists('default', $current) ? $current['default'] : $alt;
        if ($previous_default !== $default) {
            $change['default'] = true;
        }

        return $change;
    }

    /**
     * @param mixed $value
     */
    public function quote($value, ?string $type = null, bool $quote = true, bool $escape_wildcards = false): string
    {
        return $this->getDBInstance()->quote($value, $type ?? '');
    }

    /**
     * @param resource $lob
     *
     * @throws \ilDatabaseException
     */
    public function writeLOBToFile($lob, string $file): bool
    {
        $db = $this->getDBInstance();

        if (preg_match('/^(\w+:\/\/)(.*)$/', $file, $match) && $match[1] === 'file://') {
            $file = $match[2];
        }

        $fp = @fopen($file, 'wb');
        while (!@feof($lob)) {
            $result = @fread($lob, $db->options['lob_buffer_length']);
            $read = strlen($result);
            if (@fwrite($fp, $result, $read) !== $read) {
                @fclose($fp);

                throw new ilDatabaseException('could not write to the output file');
            }
        }
        @fclose($fp);

        return true;
    }

    /**
     * @param resource $lob
     */
    public function destroyLOB($lob): bool
    {
        $lob_data = stream_get_meta_data($lob);
        $lob_index = $lob_data['wrapper_data']->lob_index;
        fclose($lob);
        if (isset($this->lobs[$lob_index])) {
            unset($this->lobs[$lob_index]);
        }

        return true;
    }


    /**
     * @throws \ilDatabaseException
     */
    public function matchPattern(array $pattern, $operator = null, $field = null): string
    {
        $db = $this->getDBInstance();

        $match = '';
        if (!is_null($operator)) {
            $operator = strtoupper((string) $operator);
            switch ($operator) {
                // case insensitive
                case 'ILIKE':
                    if (is_null($field)) {
                        throw new ilDatabaseException('case insensitive LIKE matching requires passing the field name');
                    }
                    $db->loadModule('Function');
                    $match = $db->lower($field) . ' LIKE ';
                    break;
                    // case sensitive
                case 'LIKE':
                    $match = is_null($field) ? 'LIKE ' : $field . ' LIKE ';
                    break;
                default:
                    throw new ilDatabaseException('not a supported operator type:' . $operator);
            }
        }
        $match .= "'";
        foreach ($pattern as $key => $value) {
            if ($key % 2 !== 0) {
                $match .= $value;
            } else {
                if ($operator === 'ILIKE') {
                    $value = strtolower((string) $value);
                }
                // @Todo: ilDBPdo::escape & escapePattern do nothing, probably quote instead.
                $escaped = $db->escape($value);
                $match .= $db->escapePattern($escaped);
            }
        }
        $match .= "'";
        $match .= $this->patternEscapeString();

        return $match;
    }

    public function patternEscapeString(): string
    {
        return '';
    }

    /**
     * @return mixed
     */
    public function mapNativeDatatype(array $field)
    {
        $db = $this->getDBInstance();
        $db_type = strtok($field['type'], '(), ');
        if (!empty($db->options['nativetype_map_callback'][$db_type])) {
            return call_user_func_array($db->options['nativetype_map_callback'][$db_type], [$db, $field]);
        }

        return $this->mapNativeDatatypeInternal($field);
    }

    /**
     * @return mixed
     */
    public function mapPrepareDatatype(string $type)
    {
        $db = $this->getDBInstance();

        if (!empty($db->options['datatype_map'][$type])) {
            $type = $db->options['datatype_map'][$type];
            if (!empty($db->options['datatype_map_callback'][$type])) {
                $parameter = ['type' => $type];

                return call_user_func_array(
                    $db->options['datatype_map_callback'][$type],
                    [&$db, __FUNCTION__, $parameter]
                );
            }
        }

        return $type;
    }

    #[\Override]
    public function getTypeDeclaration(array $field): string
    {
        $db = $this->getDBInstance();

        switch ($field['type']) {
            case 'text':
                if (empty($field['length']) && array_key_exists('default', $field)) {
                    $field['length'] = $db->varchar_max_length ?? null;
                }
                $length = empty($field['length']) ? false : $field['length'];
                $fixed = empty($field['fixed']) ? false : $field['fixed'];
                if ($fixed) {
                    return $length ? 'CHAR(' . $length . ')' : 'CHAR(255)';
                }
                return $length ? 'VARCHAR(' . $length . ')' : 'TEXT';

            case 'clob':
                if (!empty($field['length'])) {
                    $length = $field['length'];
                    if ($length <= 255) {
                        return 'TINYTEXT';
                    }

                    if ($length <= 65532) {
                        return 'TEXT';
                    }

                    if ($length <= 16_777_215) {
                        return 'MEDIUMTEXT';
                    }
                }

                return 'LONGTEXT';
            case 'blob':
                if (!empty($field['length'])) {
                    $length = $field['length'];
                    if ($length <= 255) {
                        return 'TINYBLOB';
                    }

                    if ($length <= 65532) {
                        return 'BLOB';
                    }

                    if ($length <= 16_777_215) {
                        return 'MEDIUMBLOB';
                    }
                }

                return 'LONGBLOB';
            case 'integer':
                if (!empty($field['length'])) {
                    $length = $field['length'];
                    if ($length <= 1) {
                        return 'TINYINT';
                    }

                    if ($length === 2) {
                        return 'SMALLINT';
                    }

                    if ($length === 3) {
                        return 'MEDIUMINT';
                    }

                    if ($length === 4) {
                        return 'INT';
                    }

                    if ($length > 4) {
                        return 'BIGINT';
                    }
                }

                return 'INT';
            case 'boolean':
                return 'TINYINT(1)';
            case 'date':
                return 'DATE';
            case 'time':
                return 'TIME';
            case 'timestamp':
                return 'DATETIME';
            case 'float':
                return 'DOUBLE';
            case 'decimal':
                $length = empty($field['length']) ? 18 : $field['length'];
                // @Todo: Change property access to method call.
                $scale = empty($field['scale']) ? $db->options['decimal_places'] : $field['scale'];

                return 'DECIMAL(' . $length . ',' . $scale . ')';
        }

        return '';
    }

    /**
     * @throws \ilDatabaseException
     */
    private function mapNativeDatatypeInternal(array $field): array
    {
        $db_type = strtolower((string) $field['type']);
        $db_type = strtok($db_type, '(), ');
        if ($db_type === 'national') {
            $db_type = strtok('(), ');
        }
        if (!empty($field['length'])) {
            $length = strtok($field['length'], ', ');
            $decimal = strtok(', ');
        } else {
            $length = strtok('(), ');
            $decimal = strtok('(), ');
        }
        $type = [];
        $unsigned = $fixed = null;
        switch ($db_type) {
            case 'tinyint':
                $type[] = 'integer';
                $type[] = 'boolean';
                if (preg_match('/^(is|has)/', (string) $field['name'])) {
                    $type = array_reverse($type);
                }
                $unsigned = preg_match('/ unsigned/i', (string) $field['type']);
                $length = 1;
                break;
            case 'smallint':
                $type[] = 'integer';
                $unsigned = preg_match('/ unsigned/i', (string) $field['type']);
                $length = 2;
                break;
            case 'mediumint':
                $type[] = 'integer';
                $unsigned = preg_match('/ unsigned/i', (string) $field['type']);
                $length = 3;
                break;
            case 'int':
            case 'integer':
                $type[] = 'integer';
                $unsigned = preg_match('/ unsigned/i', (string) $field['type']);
                $length = 4;
                break;
            case 'bigint':
                $type[] = 'integer';
                $unsigned = preg_match('/ unsigned/i', (string) $field['type']);
                $length = 8;
                break;
            case 'tinytext':
            case 'mediumtext':
            case 'longtext':
            case 'text':
            case 'varchar':
                $fixed = false;
                // no break
            case 'string':
            case 'char':
                $type[] = 'text';
                if ($length == '1') {
                    $type[] = 'boolean';
                    if (preg_match('/^(is|has)/', (string) $field['name'])) {
                        $type = array_reverse($type);
                    }
                } elseif (str_contains($db_type, 'text')) {
                    $type[] = 'clob';
                    if ($decimal === 'binary') {
                        $type[] = 'blob';
                    }
                }
                if ($fixed !== false) {
                    $fixed = true;
                }
                break;
            case 'enum':
                $type[] = 'text';
                preg_match_all('/\'.+\'/U', (string) $field['type'], $matches);
                $length = 0;
                $fixed = false;
                if (is_array($matches)) {
                    foreach ($matches[0] as $value) {
                        $length = max($length, strlen($value) - 2);
                    }
                    if ($length == '1' && count($matches[0]) === 2) {
                        $type[] = 'boolean';
                        if (preg_match('/^(is|has)/', (string) $field['name'])) {
                            $type = array_reverse($type);
                        }
                    }
                }
                $type[] = 'integer';
                // no break
            case 'set':
                $fixed = false;
                $type[] = 'text';
                $type[] = 'integer';
                break;
            case 'date':
                $type[] = 'date';
                $length = null;
                break;
            case 'datetime':
            case 'timestamp':
                $type[] = 'timestamp';
                $length = null;
                break;
            case 'time':
                $type[] = 'time';
                $length = null;
                break;
            case 'float':
            case 'double':
            case 'real':
                $type[] = 'float';
                $unsigned = preg_match('/ unsigned/i', (string) $field['type']);
                break;
            case 'unknown':
            case 'decimal':
            case 'numeric':
                $type[] = 'decimal';
                $unsigned = preg_match('/ unsigned/i', (string) $field['type']);
                if ($decimal !== false) {
                    $length = $length . ',' . $decimal;
                }
                break;
            case 'tinyblob':
            case 'mediumblob':
            case 'longblob':
            case 'blob':
                $type[] = 'blob';
                $length = null;
                break;
            case 'binary':
            case 'varbinary':
                $type[] = 'blob';
                break;
            case 'year':
                $type[] = 'integer';
                $type[] = 'date';
                $length = null;
                break;
            default:
                throw new ilDatabaseException('unknown database attribute type: ' . $db_type);
        }

        if ((int) $length <= 0) {
            $length = null;
        }

        return [ $type, $length, $unsigned, $fixed ];
    }
}
