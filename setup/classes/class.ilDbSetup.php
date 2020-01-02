<?php
require_once('./Services/Database/classes/class.ilDBWrapperFactory.php');
require_once('./Services/Database/classes/class.ilDBConstants.php');

/**
 * Class ilDbSetup
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDbSetup
{
    const STATUS_OK = 1;
    const STATUS_FAILURE = 2;
    /**
     * @var ilClient
     */
    protected $client;
    /**
     * @var
     */
    protected $status = self::STATUS_FAILURE;
    /**
     * @var ilDbSetup[]
     */
    protected static $instances = array();
    /**
     * @var ilDBInterface
     */
    protected $ilDBInterface;
    /**
     * @var string
     */
    protected $sql_dump_file = './setup/sql/ilias3.sql';


    /**
     * ilDbSetup constructor.
     *
     * @param \ilClient $client
     */
    protected function __construct(\ilClient $client)
    {
        $this->client = $client;
        $this->ilDBInterface = ilDBWrapperFactory::getWrapper($client->getDbType());
        $this->ilDBInterface->initFromIniFile($this->client->ini);
    }


    /**
     * @param \ilClient $client
     * @return ilDbSetup
     */
    public static function getInstanceForClient(\ilClient $client)
    {
        if (empty(self::$instances[$client->getId()])) {
            self::$instances[$client->getId()] = new self($client);
        }

        return self::$instances[$client->getId()];
    }

    /**
     * @param \ilClient $client
     * @return \ilDbSetup
     */
    public static function getNewInstanceForClient(\ilClient $client) : \ilDbSetup
    {
        return new self($client);
    }

    /**
     * @param $client_name
     * @param $dbname
     * @param string $host
     * @param string $username
     * @param string $password
     * @param string $type
     * @return \ilDbSetup
     */
    public static function getInstanceForNewClient($client_name, $dbname, $host = 'localhost', $username = 'root', $password = '', $type = ilDBConstants::TYPE_PDO_MYSQL_INNODB)
    {
        require_once('./setup/classes/class.ilClient.php');
        require_once('./Services/Init/classes/class.ilIniFile.php');
        require_once('./setup/classes/class.ilDBConnections.php');

        $ilClient = new ilClient($client_name, new ilDBConnections());
        $ilClient->init();
        $ilClient->setDbHost($host);
        $ilClient->setDbName($dbname);
        $ilClient->setDbUser($username);
        $ilClient->setDbPass($password);
        $ilClient->setDbType($type);
        $ilClient->writeIni();

        return self::getInstanceForClient($ilClient);
    }


    /**
     * @param $a_collation
     * @return bool|mixed
     */
    public function createDatabase($a_collation)
    {
        if ($this->isConnectable()) {
            switch ($this->ilDBInterface->getDBType()) {
                case ilDBConstants::TYPE_PDO_MYSQL_MYISAM:
                case ilDBConstants::TYPE_PDO_MYSQL_INNODB:
                case ilDBConstants::TYPE_MYSQL:
                case ilDBConstants::TYPE_GALERA:
                case ilDBConstants::TYPE_INNODB:
                case ilDBConstants::TYPE_PDO_POSTGRE:
                    $clientIniFile = $this->client->ini;

                    if (!$this->ilDBInterface->createDatabase($clientIniFile->readVariable("db", "name"), 'utf8', $a_collation)) {
                        return false;
                    }
                    $this->ilDBInterface->initFromIniFile($this->getClient()->ini);

                    return $this->ilDBInterface->connect();
                    break;
            }
        }

        return false;
    }


    public function provideGlobalDB()
    {
        global $DIC;

        if ($DIC->offsetExists('ilDB')) {
            $DIC->offsetUnset('ilDB');
        }

        $GLOBALS["ilDB"] = $this->ilDBInterface;
        $DIC["ilDB"] = $this->ilDBInterface;
        $this->client->db = $this->ilDBInterface; // TODO ugly and dirty, but ilClient requires it
    }


    public function revokeGlobalDB()
    {
        $GLOBALS["ilDB"] = null;
        $this->client->db = null; // TODO ugly and dirty, but ilClient requires it
    }


    /**
     * @param $fp
     * @param $delim
     * @return string
     */
    protected function getline($fp, $delim)
    {
        $result = "";
        while (!feof($fp)) {
            $tmp = fgetc($fp);
            if ($tmp == $delim) {
                return $result;
            }
            $result .= $tmp;
        }

        return $result;
    }


    /**
     * @description legacy version of readdump
     * @deprecated  use readDumpUltraSmall
     * @return bool
     */
    protected function readDump()
    {
        $fp = fopen($this->getSqlDumpFile(), 'r');
        $q = '';
        while (!feof($fp)) {
            $line = trim($this->getline($fp, "\n"));

            if ($line != "" && substr($line, 0, 1) != "#" && substr($line, 0, 1) != "-") {
                if (substr($line, -1) == ";") {
                    //query is complete
                    $q .= " " . substr($line, 0, -1);
                    try {
                        $r = $this->ilDBInterface->query($q);
                    } catch (ilDatabaseException $e) {
                        return false;
                    }

                    unset($q);
                    unset($line);
                } else {
                    $q .= " " . $line;
                }
            }
        }

        fclose($fp);
    }


    /**
     * @description legacy version of readdump
     * @deprecated  use readDumpUltraSmall
     * @return bool
     */
    protected function readDumpSmall()
    {
        $sql = file_get_contents($this->getSqlDumpFile());
        $lines = explode(';', $sql);
        foreach ($lines as $line) {
            if (strlen($line) > 0) {
                $this->ilDBInterface->manipulate($line);
            }
        }

        return true;
    }


    /**
     * @return bool
     */
    protected function readDumpUltraSmall()
    {
        $sql = file_get_contents($this->getSqlDumpFile());
        $re = $this->ilDBInterface->prepareManip($sql);
        $this->ilDBInterface->execute($re);

        return true;
    }


    /**
     * @return bool
     */
    public function installDatabase()
    {
        if ($this->canDatabaseBeInstalled()) {
            $this->provideGlobalDB();
            switch ($this->ilDBInterface->getDBType()) {
                case ilDBConstants::TYPE_PDO_MYSQL_MYISAM:
                case ilDBConstants::TYPE_MYSQL:
                    $this->ilDBInterface->connect();
                    //$this->dropTables();
                    //$this->readDump();
                    $this->readDumpUltraSmall();
                    $this->getClient()->db_installed = true;

                    return true;

                    break;
                case ilDBConstants::TYPE_PDO_MYSQL_INNODB:
                case ilDBConstants::TYPE_INNODB:
                case ilDBConstants::TYPE_GALERA:
                case ilDBConstants::TYPE_PDO_POSTGRE:
                case ilDBConstants::TYPE_POSTGRES:
                    include_once("./setup/sql/ilDBTemplate.php");
                    setupILIASDatabase();

                    return true;
                    break;
            }
        }

        return false;
    }


    /**
     * @return bool
     */
    public function isDatabaseExisting()
    {
        if (!$this->isConnectable()) {
            return false;
        }
        if (!$this->isDatabaseConnectable()) {
            return false;
        }

        return true;
    }


    /**
     * @return bool|mixed
     */
    public function isConnectable($keep_connection = false)
    {
        switch ($this->ilDBInterface->getDBType()) {
            default:
                try {
                    $connect = $this->ilDBInterface->connect();
                } catch (PDOException $e) {
                    $connect = ($e->getCode() == 1049);
                }
                break;
        }
        if ($keep_connection && $connect) {
            $this->provideGlobalDB();
        }

        if (!$connect) {
            $this->client->setError('Database can\'t be reached. Please check the credentials and if database exists');
        }

        return $connect;
    }


    /**
     * @return bool
     */
    public function isDatabaseConnectable()
    {
        if (!$this->isConnectable()) {
            return false;
        }

        return $this->ilDBInterface->connect(true);
    }


    /**
     * @return bool
     */
    public function isDatabaseInstalled()
    {
        if (!$this->isDatabaseExisting()) {
            return false;
        }

        $target = array( 'usr_data', 'object_data', 'object_reference' );

        return count(array_intersect($this->ilDBInterface->listTables(), $target)) == count($target);
    }


    /**
     * @return bool
     */
    protected function canDatabaseBeInstalled()
    {
        $connectable = $this->isDatabaseConnectable();
        $installed = $this->isDatabaseInstalled();

        return ($connectable && !$installed);
    }


    /**
     * @return ilClient
     */
    public function getClient()
    {
        return $this->client;
    }


    /**
     * @param ilClient $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }


    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }


    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }


    /**
     * @return string
     */
    public function getSqlDumpFile()
    {
        return $this->sql_dump_file;
    }


    /**
     * @param string $sql_dump_file
     */
    public function setSqlDumpFile($sql_dump_file)
    {
        $this->sql_dump_file = $sql_dump_file;
    }


    public function dropTables()
    {
        foreach ($this->ilDBInterface->listTables() as $table) {
            $this->ilDBInterface->manipulate('DROP TABLE ' . $table);
        }
    }


    /**
     * @return \ilDBInterface
     */
    public function getIlDBInterface()
    {
        return $this->ilDBInterface;
    }


    /**
     * @param \ilDBInterface $ilDBInterface
     */
    public function setIlDBInterface($ilDBInterface)
    {
        $this->ilDBInterface = $ilDBInterface;
    }
}
