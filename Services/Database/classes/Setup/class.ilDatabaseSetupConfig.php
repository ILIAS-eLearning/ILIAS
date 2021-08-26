<?php declare(strict_types=1);

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;
use ILIAS\Data\Password;

class ilDatabaseSetupConfig implements Setup\Config
{
    const DEFAULT_COLLATION = "utf8_general_ci";
    const DEFAULT_PATH_TO_DB_DUMP = "./setup/sql/ilias3.sql";

    protected string $type;

    protected string $host;

    protected ?string $port = null;

    protected string $database;

    /**
     * @var bool
     */
    protected $create_database;

    protected string $collation;

    protected string $user;

    protected ?\ILIAS\Data\Password $password = null;

    protected string $path_to_db_dump;

    public function __construct(
        $type,
        string $host,
        string $database,
        string $user,
        Password $password = null,
        bool $create_database = true,
        string $collation = null,
        string $port = null,
        string $path_to_db_dump = null
    ) {
        if (!in_array($type, \ilDBConstants::getInstallableTypes())) {
            throw new \InvalidArgumentException(
                "Unknown database type: $type"
            );
        }
        if ($collation && !in_array(trim($collation), \ilDBConstants::getAvailableCollations())) {
            throw new \InvalidArgumentException(
                "Unknown collation: $collation"
            );
        }
        $this->type = trim($type);
        $this->host = trim($host);
        $this->database = trim($database);
        $this->user = trim($user);
        $this->password = $password;
        $this->create_database = $create_database;
        $this->collation = $collation ? trim($collation) : self::DEFAULT_COLLATION;
        $this->port = $port;
        $this->path_to_db_dump = $path_to_db_dump ?? self::DEFAULT_PATH_TO_DB_DUMP;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getHost() : string
    {
        return $this->host;
    }

    public function getPort() : ?string
    {
        return $this->port;
    }

    public function getDatabase() : string
    {
        return $this->database;
    }

    public function getCreateDatabase() : bool
    {
        return $this->create_database;
    }

    public function getCollation() : string
    {
        return $this->collation;
    }

    public function getUser() : string
    {
        return $this->user;
    }

    public function getPassword() : ?Password
    {
        return $this->password;
    }

    public function getPathToDBDump() : string
    {
        return $this->path_to_db_dump;
    }

    /**
     * Adapter to current database-handling via a mock of \ilIniFile.
     */
    public function toMockIniFile() : \ilIniFile
    {
        return new class($this) extends \ilIniFile {
            /**
             * reads a single variable from a group
             * @access	public
             * @param	string		group name
             * @param	string		value
             * @return mixed|void return value string or boolean 'false' on failure
             */
            public function readVariable($a_group, $a_var_name)
            {
                if ($a_group !== "db") {
                    throw new \LogicException(
                        "Can only access db-config via this mock."
                    );
                }
                switch ($a_var_name) {
                    case "user":
                        return $this->config->getUser();
                    case "host":
                        return $this->config->getHost();
                    case "port":
                        return $this->config->getPort();
                    case "pass":
                        $pw = $this->config->getPassword();
                        return $pw ? $pw->toString() : null;
                    case "name":
                        return $this->config->getDatabase();
                    case "type":
                        return $this->config->getType();
                    default:
                        throw new \LogicException(
                            "Cannot provide variable '$a_var_name'"
                        );
                }
            }

            /** @noinspection MagicMethodsValidityInspection */
            public function __construct(\ilDatabaseSetupConfig $config)
            {
                $this->config = $config;
            }
            public function read(): void
            {
                throw new \LogicException("Just a mock here...");
            }
            public function parse(): void
            {
                throw new \LogicException("Just a mock here...");
            }
            public function fixIniFile(): void
            {
                throw new \LogicException("Just a mock here...");
            }
            public function write(): void
            {
                throw new \LogicException("Just a mock here...");
            }
            public function show(): void
            {
                throw new \LogicException("Just a mock here...");
            }
            public function getGroupCount(): void
            {
                throw new \LogicException("Just a mock here...");
            }
            public function readGroups(): void
            {
                throw new \LogicException("Just a mock here...");
            }
            public function groupExists($a_group_name): void
            {
                throw new \LogicException("Just a mock here...");
            }
            public function readGroup($a_group_name): void
            {
                throw new \LogicException("Just a mock here...");
            }
            public function addGroup($a_group_name): void
            {
                throw new \LogicException("Just a mock here...");
            }
            public function removeGroup($a_group_name): void
            {
                throw new \LogicException("Just a mock here...");
            }
            public function variableExists($a_group, $a_var_name): void
            {
                throw new \LogicException("Just a mock here...");
            }
            public function setVariable($a_group_name, $a_var_name, $a_var_value): void
            {
                throw new \LogicException("Just a mock here...");
            }
            public function error($a_errmsg): void
            {
                throw new \LogicException("Just a mock here...");
            }
            public function getError(): void
            {
                throw new \LogicException("Just a mock here...");
            }
        };
    }
}
