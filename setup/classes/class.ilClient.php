<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Client Management
*
* @author Sascha Hofmann <shofmann@databay.de>
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*/
class ilClient
{
    public $id;					// client_id (md5 hash)
    public $dir;					// directory name in ilias/clients/
    public $name;					// installation name
    public $db_exists = false;		// db exists?
    public $db_installed = false;	// db installed?

    public $client_defaults;		// default settings
    public $status;				// contains status infos about setup process (todo: move function to this class)
    public $setup_ok = false;		// if client setup was finished at least once, this is set to true
    public $nic_status;			// contains received data of ILIAS-NIC server when registering
    /**
     * @var string
     */
    public $error = '';
    /**
     * @var ilDBInterface
     */
    public $db;
    /**
     * @var ilIniFile
     */
    public $ini;

    /**
     * @var ilDbSetup|null
     */
    protected $db_setup = null;


    /**
     * ilClient constructor.
     *
     * @param $a_client_id
     * @param $a_db_connections
     */
    public function __construct($a_client_id, $a_db_connections)
    {
        if ($a_client_id) {
            $this->id = $a_client_id;
            $this->ini_file_path = ILIAS_ABSOLUTE_PATH . "/" . ILIAS_WEB_DIR . "/" . $this->getId() . "/client.ini.php";
        }

        $this->db_connections = $a_db_connections;

        // set path default.ini
        $this->client_defaults = ILIAS_ABSOLUTE_PATH . "/setup/client.master.ini.php";
    }


    /**
     * @param bool $cached
     * @return \ilDbSetup
     */
    public function getDBSetup($cached = true)
    {
        require_once('./setup/classes/class.ilDbSetup.php');

        if ($cached) {
            if (is_null($this->db_setup)) {
                $this->db_setup = \ilDbSetup::getNewInstanceForClient($this);
            }
            return $this->db_setup;
        }


        return \ilDbSetup::getNewInstanceForClient($this);
    }

    /**
    * init client
    * load client.ini and set some constants
    * @return	boolean
    */
    public function init()
    {
        $this->ini = new ilIniFile($this->ini_file_path);

        // load defaults only if no client.ini was found
        if (!@file_exists($this->ini_file_path)) {
            //echo "<br>A-".$this->ini_file_path."-";
            $this->ini->GROUPS = parse_ini_file($this->client_defaults, true);

            return false;
        }

        // read client.ini
        if (!$this->ini->read()) {
            $this->error = get_class($this) . ": " . $this->ini->getError();

            return false;
        }

        // only for ilias main
        define("CLIENT_WEB_DIR", ILIAS_ABSOLUTE_PATH . "/" . ILIAS_WEB_DIR . "/" . $this->getId());
        define("CLIENT_DATA_DIR", ILIAS_DATA_DIR . "/" . $this->getId());
        define("DEVMODE", $this->ini->readVariable('system', 'DEVMODE'));
        define("ROOT_FOLDER_ID", $this->ini->readVariable('system', 'ROOT_FOLDER_ID'));
        define("SYSTEM_FOLDER_ID", $this->ini->readVariable('system', 'SYSTEM_FOLDER_ID'));
        define("ROLE_FOLDER_ID", $this->ini->readVariable('system', 'ROLE_FOLDER_ID'));
        define("ANONYMOUS_USER_ID", 13);
        define("ANONYMOUS_ROLE_ID", 14);
        define("SYSTEM_USER_ID", 6);
        define("SYSTEM_ROLE_ID", 2);

        $this->db_exists = $this->getDBSetup()->isConnectable();
        $this->getDBSetup()->provideGlobalDB();
        if ($this->db_exists) {
            $this->db_installed = $this->getDBSetup()->isDatabaseInstalled();
        }

        return true;
    }


    public function provideGlobalDB()
    {
        $this->getDBSetup()->provideGlobalDB();
    }


    public function revokeGlobalDB()
    {
        $this->getDBSetup()->provideGlobalDB();
    }

    /**
    * get client id
    * @return	string	client id
    */
    public function getId()
    {
        return $this->id;
    }

    /**
    * set client id
    * @param	string	client id
    */
    public function setId($a_client_id)
    {
        $this->id = $a_client_id;
        $this->webspace_dir = ILIAS_ABSOLUTE_PATH . "/" . ILIAS_WEB_DIR . "/" . $this->id;
    }

    /**
    * get client name
    * @return	string	client name
    */
    public function getName()
    {
        return $this->ini->readVariable("client", "name");
    }

    /**
    * set client name
    * @param	string	client name
    */
    public function setName($a_str)
    {
        $this->ini->setVariable("client", "name", $a_str);
    }

    /**
    * get client description
    * @return	string	client description
    */
    public function getDescription()
    {
        return $this->ini->readVariable("client", "description");
    }

    /**
    * set client description
    * @param	string	client description
    */
    public function setDescription($a_str)
    {
        $this->ini->setVariable("client", "description", $a_str);
    }

    /**
    * get mysql version
    */
    /*	function getMySQLVersion()
        {
            return mysql_get_server_info();
        }*/

    /**
    * Get DB object
    */
    public function getDB()
    {
        return $this->db;
    }

    /**
    * connect to client database
    * @return	boolean	true on success
    */
    public function connect()
    {
        // check parameters
        // To support oracle tnsnames.ora dbname is not required
        if (!$this->getdbHost() || !$this->getdbUser()) {
            $this->error = "empty_fields";

            return false;
        }

        include_once("./Services/Database/classes/class.ilDBWrapperFactory.php");
        $this->db = ilDBWrapperFactory::getWrapper($this->getdbType());
        $this->db->setDBUser($this->getdbUser());
        $this->db->setDBPort($this->getdbPort());
        $this->db->setDBPassword($this->getdbPass());
        $this->db->setDBHost($this->getdbHost());
        $this->db->setDBName($this->getdbName());
        $con = $this->db->connect(true);

        if (!$con) {
            $this->error = "Database connection failed.";
            return false;
        }
        $GLOBALS["ilDB"] = $this->db;

        if ($GLOBALS["DIC"]->offsetExists("ilDB")) {
            $GLOBALS["DIC"]->offsetUnset("ilDB");
        }

        $GLOBALS["DIC"]["ilDB"] = function ($c) {
            return $GLOBALS["ilDB"];
        };

        $this->db_exists = true;
        return true;
    }

    /**
    * check if client db is installed
    * @param	object	db object
    * @return	boolean	true if installed
    */
    public function isInstalledDB(&$a_db)
    {
        if (method_exists($a_db, 'loadModule')) {
            $a_db->loadModule('Manager');
        }
        if (!$tables = $a_db->listTables()) {
            return false;
        }

        // check existence of some basic tables from ilias3 to determine if ilias3 is already installed in given database
        if (in_array("object_data", $tables) and in_array("object_reference", $tables) and in_array("usr_data", $tables) and in_array("rbac_ua", $tables)) {
            $this->db_installed = true;
            return true;
        }
        $this->db_installed = false;
        return false;
    }

    /**
    * set the dsn and dsn_host
    */
    public function setDSN()
    {
        switch ($this->getDbType()) {
            case "postgres":
                $db_port_str = "";
                if (trim($this->getdbPort()) != "") {
                    $db_port_str = ":" . $this->getdbPort();
                }
                $this->dsn_host = "pgsql://" . $this->getdbUser() . ":" . $this->getdbPass() . "@" . $this->getdbHost() . $db_port_str;
                $this->dsn = "pgsql://" . $this->getdbUser() . ":" . $this->getdbPass() . "@" . $this->getdbHost() . $db_port_str . "/" . $this->getdbName();
                break;

            case "mysql":
            case "innodb":
            default:
                $db_port_str = "";
                if (trim($this->getdbPort()) != "") {
                    $db_port_str = ":" . $this->getdbPort();
                }
                $this->dsn_host = "mysql://" . $this->getdbUser() . ":" . $this->getdbPass() . "@" . $this->getdbHost() . $db_port_str;
                $this->dsn = "mysql://" . $this->getdbUser() . ":" . $this->getdbPass() . "@" . $this->getdbHost() . $db_port_str . "/" . $this->getdbName();
                break;
        }
    }

    /**
    * set the host
    * @param	string
    */
    public function setDbHost($a_str)
    {
        $this->ini->setVariable("db", "host", $a_str);
    }

    /**
    * get db host
    * @return	string	db host
    *
    */
    public function getDbHost()
    {
        return $this->ini->readVariable("db", "host");
    }

    /**
    * set the name of database
    * @param	string
    */
    public function setDbName($a_str)
    {
        $this->ini->setVariable("db", "name", $a_str);
    }

    /**
    * get name of database
    * @return	string	name of database
    */
    public function getDbName()
    {
        return $this->ini->readVariable("db", "name");
    }

    /**
    * set db user
    * @param	string	db user
    */
    public function setDbUser($a_str)
    {
        $this->ini->setVariable("db", "user", $a_str);
    }

    /**
    * get db user
    * @return	string	db user
    */
    public function getDbUser()
    {
        return $this->ini->readVariable("db", "user");
    }

    /**
    * get db port
    * @return	string	db port
    */
    public function getDbPort()
    {
        return $this->ini->readVariable("db", "port");
    }

    /**
    * set db port
    * @param	string
    */
    public function setDbPort($a_str)
    {
        $this->ini->setVariable("db", "port", $a_str);
    }

    /**
    * set db password
    * @param	string
    */
    public function setDbPass($a_str)
    {
        $this->ini->setVariable("db", "pass", $a_str);
    }

    /**
    * get db password
    * @return	string	db password
    */
    public function getDbPass()
    {
        return $this->ini->readVariable("db", "pass");
    }

    /**
    * set the slave active
    * @param int
    */
    public function setDbSlaveActive($a_act)
    {
        $this->ini->setVariable("db", "slave_active", (int) $a_act);
    }

    /**
    * get slave active
    * @return int active
    *
    */
    public function getDbSlaveActive()
    {
        return (int) $this->ini->readVariable("db", "slave_active");
    }

    /**
    * set the slave host
    * @param	string
    */
    public function setDbSlaveHost($a_str)
    {
        $this->ini->setVariable("db", "slave_host", $a_str);
    }

    /**
    * get db slave host
    * @return	string	db host
    *
    */
    public function getDbSlaveHost()
    {
        return $this->ini->readVariable("db", "slave_host");
    }

    /**
    * set the name of slave database
    * @param	string
    */
    public function setDbSlaveName($a_str)
    {
        $this->ini->setVariable("db", "slave_name", $a_str);
    }

    /**
    * get name of slave database
    * @return	string	name of database
    */
    public function getDbSlaveName()
    {
        return $this->ini->readVariable("db", "slave_name");
    }

    /**
    * set slave db user
    * @param	string	db user
    */
    public function setDbSlaveUser($a_str)
    {
        $this->ini->setVariable("db", "slave_user", $a_str);
    }

    /**
    * get slave db user
    * @return	string	db user
    */
    public function getDbSlaveUser()
    {
        return $this->ini->readVariable("db", "slave_user");
    }

    /**
    * get slave db port
    * @return	string	db port
    */
    public function getDbSlavePort()
    {
        return $this->ini->readVariable("db", "slave_port");
    }

    /**
    * set slave db port
    * @param	string
    */
    public function setDbSlavePort($a_str)
    {
        $this->ini->setVariable("db", "slave_port", $a_str);
    }

    /**
    * set slave db password
    * @param	string
    */
    public function setDbSlavePass($a_str)
    {
        $this->ini->setVariable("db", "slave_pass", $a_str);
    }

    /**
    * get slave db password
    * @return	string	db password
    */
    public function getDbSlavePass()
    {
        return $this->ini->readVariable("db", "slave_pass");
    }

    /**
    * set the type of database
    * @param	string
    */
    public function setDbType($a_str)
    {
        $this->ini->setVariable("db", "type", $a_str);
    }

    /**
    * get type of database
    * @return	string	name of database
    */
    public function getDbType()
    {
        $val = $this->ini->readVariable("db", "type");
        if ($val == "") {
            return "mysql";
        } else {
            return $val;
        }
    }

    /**
    * get client datadir path
    * @return	string	client datadir path
    */
    public function getDataDir()
    {
        return ILIAS_DATA_DIR . "/" . $this->getId();
    }

    /**
    * get client webspacedir path
    * @return 	string	clietn webspacedir path
    */
    public function getWebspaceDir()
    {
        return ILIAS_ABSOLUTE_PATH . "/" . ILIAS_WEB_DIR . "/" . $this->getId();
    }


    /**
    * check database connection with database name
    * @return	boolean
    */
    public function checkDatabaseExists($a_keep_connection = false)
    {
        return $this->getDBSetup()->isConnectable();
        
        //try to connect to database
        $db = $this->db_connections->connectDB($this->dsn);
        if (MDB2::isError($db)) {
            return false;
        }

        if (!$this->isInstalledDB($db)) {
            return false;
        }

        // #10633
        if ($a_keep_connection) {
            $GLOBALS["ilDB"] = $this->db;
            $GLOBALS["DIC"]["ilDB"] = function ($c) {
                return $GLOBALS["ilDB"];
            };
        }

        return true;
    }

    public function reconnect()
    {
        $this->connect();
    }


    /**
     * read one value from settings table
     *
     * @access    public
     * @param    string    keyword
     * @return    string    value
     */
    public function getSetting($a_keyword)
    {
        global $ilDB;
        if (!$this->getDBSetup()->isDatabaseInstalled() || !$ilDB) {
            return false;
        }
        include_once './Services/Administration/classes/class.ilSetting.php';
        $set = new ilSetting("common", true);

        return $set->get($a_keyword);
    }

    /**
    * read all values from settings table
    * @access	public
    * @return	array	keyword/value pairs
    */
    public function getAllSettings()
    {
        include_once './Services/Administration/classes/class.ilSetting.php';
        $set = new ilSetting("common", true);
        return $set->getAll();
    }

    /**
    * write one value to settings table
    * @access	public
    * @param	string		keyword
    * @param	string		value
    * @return	boolean		true on success
    */
    public function setSetting($a_key, $a_val)
    {
        include_once './Services/Administration/classes/class.ilSetting.php';
        $set = new ilSetting("common", true);
        $set->set($a_key, $a_val);
    }

    /**
    * @param	string	url to ilias nic server
    * @return	string	url with required parameters
    */
    public function getURLStringForNIC($a_nic_url)
    {
        $settings = $this->getAllSettings();

        $inst_id = (empty($settings["inst_id"])) ? "0" : $settings["inst_id"];

        // send host information to ilias-nic
        //#18132: removed ipadr, server_port, server_software, institution, contact_title, contact_position,
        // contact_institution, contact_street, contact_pcode, contact_city, contact_country, contact_phone
        $url = $a_nic_url .
                "?cmd=getid" .
                "&inst_id=" . rawurlencode($inst_id) .
                "&hostname=" . rawurlencode($_SERVER["SERVER_NAME"]) .
                "&inst_name=" . rawurlencode($this->ini->readVariable("client", "name")) .
                "&inst_info=" . rawurlencode($this->ini->readVariable("client", "description")) .
                "&http_path=" . rawurlencode(ILIAS_HTTP_PATH) .
                "&contact_firstname=" . rawurlencode($settings["admin_firstname"]) .
                "&contact_lastname=" . rawurlencode($settings["admin_lastname"]) .
                "&contact_email=" . rawurlencode($settings["admin_email"]) .
                "&nic_key=" . rawurlencode($this->getNICkey());

        return $url;
    }

    /**
    * Connect to ILIAS-NIC
    *
    * This function establishes a HTTP connection to the ILIAS Network
    * Information Center (NIC) in order to update the ILIAS-NIC host
    * database and - in case of a newly installed system - obtain an
    * installation id at first connection.
    * This function my be put into a dedicated include file as soon
    * as there are more functions concerning the interconnection of
    * ILIAS hosts
    *
    * @param	void
    * @return	string/array	$ret	error message or data array
    */
    public function updateNIC($a_nic_url)
    {
        $max_redirects = 5;
        $socket_timeout = 5;

        require_once(__DIR__ . "/../../Services/WebServices/Curl/classes/class.ilCurlConnection.php");
        if (!ilCurlConnection::_isCurlExtensionLoaded()) {
            $this->setError("CURL-extension not loaded.");
            return false;
        }

        $url = $this->getURLStringForNIC($a_nic_url);
        $req = new ilCurlConnection($url);
        $req->init();

        $settings = $this->getAllSettings();
        if ((bool) $settings['proxy_status'] && strlen($settings['proxy_host']) && strlen($settings['proxy_port'])) {
            $req->setOpt(CURLOPT_HTTPPROXYTUNNEL, true);
            $req->setOpt(CURLOPT_PROXY, $settings["proxy_host"]);
            $req->setOpt(CURLOPT_PROXYPORT, $settings["proxy_port"]);
        }

        $req->setOpt(CURLOPT_HEADER, 1);
        $req->setOpt(CURLOPT_RETURNTRANSFER, 1);
        $req->setOpt(CURLOPT_CONNECTTIMEOUT, $socket_timeout);
        $req->setOpt(CURLOPT_MAXREDIRS, $max_redirects);
        $response = $req->exec();
        
        $req->parseResponse($response);
        $response_body = $req->getResponseBody();

        $info = $req->getInfo();
        if ($info["http_code"] != "200") {
            $this->setError("Could not connect to NIC-Server at '" . $url . "'");
            return false;
        }

        $this->nic_status = explode("\n", $response_body);

        ilLoggerFactory::getLogger('setup')->dump($this->nic_status);

        return true;
    }

    /**
    * set nic_key
    * generate nic_key if nic_key field in cust table is empty.
    * the nic_key is used for authentication update requests sent
    * to the ILIAS-NIC server.
    * @access	public
    * @return	boolean
    */
    public function setNICkey()
    {
        mt_srand((double) microtime() * 1000000);
        $nic_key = md5(str_replace(".", "", $_SERVER["SERVER_ADDR"]) +
                    mt_rand(100000, 999999));

        $this->setSetting("nic_key", $nic_key);

        $this->nic_key = $nic_key;

        return true;
    }

    /**
    * get nic_key
    * @access	public
    * @return	string	nic_key
    */
    public function getNICkey()
    {
        $this->nic_key = $this->getSetting("nic_key");

        if (empty($this->nic_key)) {
            $this->setNICkey();
        }

        return $this->nic_key;
    }

    public function getDefaultLanguage()
    {
        return $this->getSetting("language");
    }

    public function setDefaultLanguage($a_lang_key)
    {
        $this->setSetting("language", $a_lang_key);
        $this->ini->setVariable("language", "default", $a_lang_key);
        $this->ini->write();

        return true;
    }


    /**
     * get error message and clear error var
     *
     * @return    string    error message
     */
    public function getError()
    {
        $error = $this->error;
        $this->error = "";

        return $error;
    }


    /**
     * @param $error_message
     */
    public function setError($error_message)
    {
        $this->error = $error_message;
    }

    /**
    * delete client
    * @param	boolean	remove ini if true
    * @param	boolean	remove db if true
    * @param	boolean remove files if true
    * @return	array	confirmation messages
    *
    */
    public function delete($a_ini = true, $a_db = false, $a_files = false)
    {
        if ($a_ini === true and file_exists(ILIAS_ABSOLUTE_PATH . "/" . ILIAS_WEB_DIR . "/" . $this->getId() . "/client.ini.php")) {
            unlink(CLIENT_WEB_DIR . "/client.ini.php");
            $msg[] = "ini_deleted";
        }

        if ($a_db === true and $this->db_exists) {
            $this->db->query("DROP DATABASE " . $this->getDbName());
            $msg[] = "db_deleted";
        }

        if ($a_files === true and file_exists(CLIENT_WEB_DIR) and is_dir(CLIENT_WEB_DIR)) {
            // rmdir();
            ilUtil::delDir(CLIENT_WEB_DIR);
            ilUtil::delDir(CLIENT_DATA_DIR);
            $msg[] = "files_deleted";
        }

        return $msg;
    }

    /**
    * create a new client and its subdirectories
    * @return	boolean	true on success
    */
    public function create()
    {
        //var_dump($this->getDataDir());exit;
        // create base data dir
        if (!ilUtil::makeDir($this->getDataDir())) {
            $this->error = "could_not_create_base_data_dir :" . $this->getDataDir();
            return false;
        }

        // create sub dirs in base data dir
        if (!ilUtil::makeDir($this->getDataDir() . "/mail")) {
            $this->error = "could_not_create_mail_data_dir :" . $this->getDataDir() . "/mail";
            return false;
        }

        if (!ilUtil::makeDir($this->getDataDir() . "/lm_data")) {
            $this->error = "could_not_create_lm_data_dir :" . $this->getDataDir() . "/lm_data";
            return false;
        }

        if (!ilUtil::makeDir($this->getDataDir() . "/forum")) {
            $this->error = "could_not_create_forum_data_dir :" . $this->getDataDir() . "/forum";
            return false;
        }

        if (!ilUtil::makeDir($this->getDataDir() . "/files")) {
            $this->error = "could_not_create_files_data_dir :" . $this->getDataDir() . "/files";
            return false;
        }

        // create base webspace dir
        if (!ilUtil::makeDir($this->getWebspaceDir())) {
            $this->error = "could_not_create_base_webspace_dir :" . $this->getWebspaceDir();
            return false;
        }

        // create sub dirs in base webspace dir
        if (!ilUtil::makeDir($this->getWebspaceDir() . "/lm_data")) {
            $this->error = "could_not_create_lm_webspace_dir :" . $this->getWebspaceDir() . "/lm_data";
            return false;
        }

        if (!ilUtil::makeDir($this->getWebspaceDir() . "/usr_images")) {
            $this->error = "could_not_create_usr_images_webspace_dir :" . $this->getWebspaceDir() . "/usr_images";
            return false;
        }

        if (!ilUtil::makeDir($this->getWebspaceDir() . "/mobs")) {
            $this->error = "could_not_create_mobs_webspace_dir :" . $this->getWebspaceDir() . "/mobs";
            return false;
        }

        if (!ilUtil::makeDir($this->getWebspaceDir() . "/css")) {
            $this->error = "could_not_create_css_webspace_dir :" . $this->getWebspaceDir() . "/css";
            return false;
        }

        // write client ini
        if (!$this->ini->write()) {
            $this->error = get_class($this) . ": " . $this->ini->getError();
            return false;
        }

        return true;
    }

    /**
     * write init
     *
     * @param
     * @return
     */
    public function writeIni()
    {
        $this->ini->write();
    }
} // END class.ilClient
