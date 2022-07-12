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
 
use ILIAS\Setup;
use ILIAS\Data\Password;

class ilDatabaseSetupConfig implements Setup\Config
{
    const DEFAULT_COLLATION = "utf8_general_ci";
    const DEFAULT_PATH_TO_DB_DUMP = "./setup/sql/ilias3.sql";

    protected string $type;

    protected string $host;

    protected ?int $port = null;

    protected string $database;

    protected bool $create_database;

    protected string $collation;

    protected string $user;

    protected ?\ILIAS\Data\Password $password = null;

    protected string $path_to_db_dump;

    public ilDatabaseSetupConfig $config;

    public function __construct(
        string $type,
        string $host,
        string $database,
        string $user,
        Password $password = null,
        bool $create_database = true,
        string $collation = null,
        int $port = null,
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

    public function getType() : string
    {
        return $this->type;
    }

    public function getHost() : string
    {
        return $this->host;
    }

    public function getPort() : ?int
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
            public function readVariable(string $a_group, string $a_var_name) : string
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
                        return (string) $this->config->getPort();
                    case "pass":
                        $pw = $this->config->getPassword();
                        return $pw ? $pw->toString() : "";
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

            public function __construct(\ilDatabaseSetupConfig $config)
            {
                $this->config = $config;
            }
            public function read() : bool
            {
                throw new \LogicException("Just a mock here...");
            }
            public function parse() : bool
            {
                throw new \LogicException("Just a mock here...");
            }
            public function fixIniFile() : void
            {
                throw new \LogicException("Just a mock here...");
            }
            public function write() : bool
            {
                throw new \LogicException("Just a mock here...");
            }
            public function show() : string
            {
                throw new \LogicException("Just a mock here...");
            }
            public function getGroupCount() : int
            {
                throw new \LogicException("Just a mock here...");
            }
            public function readGroups() : array
            {
                throw new \LogicException("Just a mock here...");
            }
            public function groupExists(string $a_group_name) : bool
            {
                throw new \LogicException("Just a mock here...");
            }
            public function readGroup(string $a_group_name) : array
            {
                throw new \LogicException("Just a mock here...");
            }
            public function addGroup(string $a_group_name) : bool
            {
                throw new \LogicException("Just a mock here...");
            }
            public function removeGroup(string $a_group_name) : bool
            {
                throw new \LogicException("Just a mock here...");
            }
            public function variableExists(string $a_group, string $a_var_name) : bool
            {
                throw new \LogicException("Just a mock here...");
            }
            public function setVariable(string $a_group_name, string $a_var_name, string $a_var_value) : bool
            {
                throw new \LogicException("Just a mock here...");
            }
            public function error(string $a_errmsg) : bool
            {
                throw new \LogicException("Just a mock here...");
            }
            public function getError() : string
            {
                throw new \LogicException("Just a mock here...");
            }
        };
    }
}
