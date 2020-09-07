<?php
/* Copyright (c) 1998-2012mk ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./setup/classes/class.ilDBConnections.php");

/**
* Setup class
*
* class to setup ILIAS first and maintain the ini-settings and the database
*
* @author	Peter Gabriel <pgabriel@databay.de>
* @author	Sascha Hofmann <shofmann@databay.de>
* @version	$Id$
*/
class ilSetup
{

    /**
     * @var ilIniFile
     */
    public $ini;			// ini file object
    public $ini_file_path;	// full path to setup.ini, containing the client list
    public $error = "";	// error text

    public $ini_ilias_exists = false;	// control flag ilias.ini
    public $ini_client_exists = false; // control flag client.ini

    public $setup_defaults;			// ilias.master.ini
    public $ilias_nic_server = "https://nic.ilias.de/index.php";	// URL to ilias nic server

    public $preliminaries_result = array();	// preliminaries check results
    public $preliminaries = true;				//

    /**
    * sql-template-file
    * @var		string
    * @access	private
    */
    public $SQL_FILE = "./setup/sql/ilias3.sql";

    /**
    *  database connector
    *  @var		string
    *  @access	public
    */
    public $dsn = "";

    /**
    *  database handler
    *  @var		object
    *  @access	public
    */
    public $db;

    public $setup_password;		// master setup password
    public $default_client;		// client id of default client

    public $safe_mode;				// safe mode enabled (true) or disabled (false)
    public $safe_mode_exec_dir;	// contains exec_dir_path

    public $auth;					// current user is authenticated? (true)
    public $access_mode;			// if "admin", admin functions are enabled

    /**
     * @var \ilSetupPasswordManager
     */
    protected $passwordManager;

    /**
     * constructor
     * @param    $passwordManager \ilSetupPasswordManager
     * @param    boolean        user is authenticated? (true) or not (false)
     * @param    string        user is admin or common user
     */
    public function __construct(\ilSetupPasswordManager $passwordManager, $a_auth, $a_auth_type)
    {
        global $lng;

        $this->passwordManager = $passwordManager;

        $this->lng = $lng;

        $this->db_connections = new ilDBConnections();

        define("ILIAS_MODULE", "setup");

        $this->auth = ($this->checkAuth()) ? true : false;
        $this->access_mode = $a_auth_type;

        // safe mode status & exec_dir
        if ($this->safe_mode = ini_get("safe_mode")) {
            $this->safe_mode_exec_dir = ilFile::deleteTrailingSlash(ini_get("safe_mode_exec_dir"));
        }

        // set path to ilias.ini
        $this->ini_file_path = ILIAS_ABSOLUTE_PATH . "/ilias.ini.php";
        $this->setup_defaults = ILIAS_ABSOLUTE_PATH . "/setup/ilias.master.ini.php";

        // init setup.ini
        $this->ini_ilias_exists = $this->init();

        /*
        if ($this->ini_ilias_exists)
        {
            if ($this->ini->readVariable("log","path") != "")
            {
                $log->path = $this->ini->readVariable("log","path");
            }

            if ($this->ini->readVariable("log","file") != "")
            {
                $log->filename = $this->ini->readVariable("log","file");
            }

            if ($this->ini->readVariable("log","enabled") != "")
            {
                $log->enabled = $this->ini->readVariable("log","enabled");
            }
        }
        */
    }


    /**
     * @var ilClient
     */
    public $client;


    /**
     * @param $a_cl
     */
    public function setClient($a_cl)
    {
        $this->client = $a_cl;
    }


    /**
     * @return ilClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
    * init setup
    * load settings from ilias.ini if exists and sets some constants
    * @return	boolean
    */
    public function init()
    {
        // load data from setup.ini file
        $this->ini = new ilIniFile($this->ini_file_path);

        if (!$this->ini->read()) {
            $this->ini->GROUPS = parse_ini_file($this->setup_defaults, true);
            $this->error = get_class($this) . ": " . $this->ini->getError();
            return false;
        }

        $this->setup_password = $this->ini->readVariable("setup", "pass");
        $this->default_client = $this->ini->readVariable("clients", "default");

        define("ILIAS_DATA_DIR", $this->ini->readVariable("clients", "datadir"));
        define("ILIAS_WEB_DIR", $this->ini->readVariable("clients", "path"));

        return true;
    }

    /**
    * saves client.ini & updates client list in ilias.ini
    * @return	boolean
    */
    public function saveNewClient()
    {
        // save client id to session
        $_SESSION["ClientId"] = $this->client->getId();

        // create client
        if (!$this->client->create()) {
            $this->error = $this->client->getError();
            return false;
        }

        //everything okay
        $this->ini_client_exists = true;

        return true;
    }

    /**
    * update client.ini & move data dirs
    * does not work correctly at this time - DISABLED
    * @return	boolean
    */
    public function updateNewClient($a_old_client_id)
    {
        return true;
        //var_dump("<pre>",$this->client,"</pre>");exit;
        //Error Handling disabled!! caused by missing PEAR
        if ($a_old_client_id != $this->client->getId()) {
            $this->saveNewClient();

            ilUtil::delDir(ILIAS_ABSOLUTE_PATH . "/" . ILIAS_WEB_DIR . "/" . $a_old_client_id);
            ilUtil::delDir(ILIAS_DATA_DIR . "/" . $a_old_client_id);
        }

        //everything okay
        $this->ini_client_exists = true;

        return true;
    }

    /**
    * create client database
    * @return	boolean
    */
    public function createDatabase($a_collation = "")
    {
        if ($this->client->getDBSetup()->isDatabaseInstalled()) {
            $this->error = $this->lng->txt("database_exists");

            return false;
        }

        $db_setup = $this->client->getDBSetup();
        return $db_setup->createDatabase($a_collation);
    }

    /**
    * set the database data
    * @return	boolean
     * @deprecated
    */
    public function installDatabase()
    {
        if (!$this->client->getDBSetup()->isDatabaseConnectable()) {
            return false;
        }

        if ($this->client->getDBSetup()->installDatabase()) {
            $this->client->db_installed = true;

            return true;
        }

        return false;
    }


    /**
    * check if inifile exists
    * @return	boolean
    */
    public function checkIniFileExists()
    {
        $a = @file_exists($this->INI_FILE);
        return $a;
    }

    /**
    * check for writable directory
    * @param	string	directory
    * @return	array
    */
    public function checkWritable()
    {
        clearstatcache();
        if (is_writable(".")) {
            $arr["status"] = true;
            //$cdir = getcwd();
            //chdir("..");
            $arr["comment"] = getcwd();
        //chdir($cdir);
        } else {
            $arr["status"] = false;
            $arr["comment"] = $this->lng->txt("pre_folder_write_error");
            //$cdir = getcwd();
            //chdir("..");
            $arr["comment"] = getcwd() . ": " . $arr["comment"];
            //chdir($cdir);
        }

        return $arr;
    }

    /**
    * check for permission to create new folders in specified directory
    * @param	string	directory
    * @return	array
    */
    public function checkCreatable($a_dir = ".")
    {
        clearstatcache();
        if (@mkdir($a_dir . "/crst879dldsk9d", 0774)) {
            $arr["status"] = true;
            $arr["comment"] = "";

            @rmdir($a_dir . "/crst879dldsk9d");
        } else {
            $arr["status"] = false;
            //$cdir = getcwd();
            //chdir("..");
            $arr["comment"] = getcwd() . ": " . $this->lng->txt("pre_folder_create_error");
            //chdir($cdir);
        }

        return $arr;
    }

    /**
    * check cookies enabled
    * @return	array
    */
    public function checkCookiesEnabled()
    {
        global $sess;

        if ($sess->usesCookies) {
            $arr["status"] = true;
            $arr["comment"] = "";
        } else {
            $arr["status"] = false;
            $arr["comment"] = $this->lng->txt("pre_cookies_disabled");
        }

        return $arr;
    }

    /**
    * check for PHP version
    * @return	array
    */
    public function checkPHPVersion()
    {
        $version = PHP_VERSION;

        $arr["status"] = true;
        $arr["comment"] = "PHP " . $version;
        if (version_compare($version, '5.3.0', '<')) {
            $arr["status"] = false;
            $arr["comment"] = "PHP " . $version . ". " . $this->lng->txt("pre_php_version_too_low");
        }

        return $arr;
    }

    /**
    * Check MySQL
    * @return	boolean
    */
    public function checkMySQL()
    {
        global $ilDB;

        if (function_exists("mysql_query")) {
            $arr["status"] = true;
            $arr["comment"] = $this->lng->txt("pre_mysql_4_1_or_higher");
        } else {
            $arr["status"] = false;
            $arr["comment"] = $this->lng->txt("pre_mysql_missing");
        }

        return $arr;
    }

    /**
    * check authentication status
    * @return	boolean
    */
    public function checkAuth()
    {
        if ($_SESSION["auth"] === true && $_SESSION["auth_path"] == ILIAS_HTTP_PATH) {
            return true;
        }

        return false;
    }


    /**
    * Check MySQL
    * @return	boolean
    */
    public function checkDom()
    {
        global $ilDB;

        if (class_exists("DOMDocument")) {
            $arr["status"] = true;
        } else {
            $arr["status"] = false;
            $arr["comment"] = $this->lng->txt("pre_dom_missing");
        }

        return $arr;
    }

    /**
    * Check MySQL
    * @return	boolean
    */
    public function checkXsl()
    {
        global $ilDB;

        if (class_exists("XSLTProcessor")) {
            $arr["status"] = true;
        } else {
            $arr["status"] = false;
            $arr["comment"] = sprintf(
                $this->lng->txt("pre_xsl_missing"),
                "http://php.net/manual/en/book.xsl.php"
            );
        }

        return $arr;
    }

    /**
    * Check MySQL
    * @return	boolean
    */
    public function checkGd()
    {
        global $ilDB;

        if (function_exists("imagefill") && function_exists("imagecolorallocate")) {
            $arr["status"] = true;
        } else {
            $arr["status"] = false;
            $arr["comment"] = sprintf(
                $this->lng->txt("pre_gd_missing"),
                "http://php.net/manual/en/book.image.php"
            );
        }

        return $arr;
    }

    /**
    * Check Memory Limit
    * @return	boolean
    */
    public function checkMemoryLimit()
    {
        global $ilDB;

        $limit = ini_get("memory_limit");

        $limit_ok = true;
        if (is_int(strpos($limit, "M"))) {
            $limit_n = (int) $limit;
            if ($limit_n < 40) {
                $limit_ok = false;
            }
        }

        if ($limit_ok) {
            $arr["status"] = true;
            $arr["comment"] = $limit . ". " . $this->lng->txt("pre_memory_limit_recommend");
        } else {
            $arr["status"] = false;
            $arr["comment"] = $limit . ". " . $this->lng->txt("pre_memory_limit_too_low");
        }

        return $arr;
    }


    /**
     * @return array
     */
    protected function checkOpcacheSettings()
    {
        $arr = array();
        // correct-with-php5-removal FSX start
        if (version_compare(PHP_VERSION, '7.0.0', '>=')) {
            $arr["status"] = true;

            return $arr;
        }
        // correct-with-php5-removal FSX end

        $load_comments = ini_get("opcache.load_comments");
        if ($load_comments == 1) {
            $arr["status"] = true;
        } else {
            $arr["status"] = false;
            $arr["comment"] = $this->lng->txt("pre_opcache_comments");
        }

        return $arr;
    }

    /**
    * preliminaries
    *
    * check if different things are ok for setting up ilias
    * @access	private
    * @return 	array
    */
    public function queryPreliminaries()
    {
        $a = array();
        $a["php"] = $this->checkPHPVersion();
        //		$a["mysql"] = $this->checkMySQL();
        $a["root"] = $this->checkWritable();
        $a["folder_create"] = $this->checkCreatable();
        $a["cookies_enabled"] = $this->checkCookiesEnabled();
        $a["dom"] = $this->checkDom();
        $a["xsl"] = $this->checkXsl();
        $a["gd"] = $this->checkGd();
        $a["memory"] = $this->checkMemoryLimit();

        if ($this->hasOpCacheEnabled()) {
            $a["load_comments"] = $this->checkOpcacheSettings();
        }

        return $a;
    }

    /**
    * check all prliminaries
    * @return	boolean
    */
    public function checkPreliminaries()
    {
        $this->preliminaries_result = $this->queryPreliminaries();

        foreach ($this->preliminaries_result as $val) {
            if ($val["status"] === false) {
                $this->preliminaries = false;
                return false;
            }
        }

        return true;
    }

    /**
     * @return string
     */
    public function getMasterPassword() : string
    {
        return $this->ini->readVariable('setup', 'pass');
    }

    /**
     * @param string $raw
     * @return bool Returns a boolean status, whether or not the password could be set successful
     */
    public function storeMasterPassword(string $raw) : bool
    {
        $this->ini->setVariable('setup', 'pass', $this->passwordManager->encodePassword($raw));

        if ($this->ini->write() == false) {
            $this->error = $this->ini->getError();
            return false;
        }

        return true;
    }

    /**
     * @param  string $raw
     * @return bool
     * @throws \ilUserException
     */
    public function verifyMasterPassword(string $raw) : bool
    {
        $passwordReHashCallback = function ($raw) {
            $this->storeMasterPassword($raw);
        };

        return $this->passwordManager->verifyPassword(
            $this->getMasterPassword(),
            $raw,
            $passwordReHashCallback
        );
    }

    /**
    * process client login
    * @param	array
    * @return	boolean
    */
    public function loginAsClient($a_auth_data)
    {
        global $ilDB;

        if (empty($a_auth_data["client_id"])) {
            $this->error = "no_client_id";
            return false;
        }

        if (empty($a_auth_data["username"])) {
            $this->error = "no_username";
            return false;
        }

        if (empty($a_auth_data["password"])) {
            $this->error = "no_password";
            return false;
        }

        if (!$this->newClient($a_auth_data["client_id"])) { //TT: comment out to get around null db
            $this->error = "unknown_client_id";
            unset($this->client);
            return false;
        }

        if (!$this->client->db_exists) {
            $this->error = "no_db_connect_consult_admin";
            unset($this->client);
            return false;
        }

        $s1 = $this->client->db->query("SELECT value from settings WHERE keyword = " .
            $this->client->db->quote('system_role_id', 'text'));
        $r1 = $this->client->db->fetchAssoc($s1);
        $system_role_id = $r1["value"];

        $add_usrfields = '';
        if ($this->client->db->tableColumnExists('usr_data', 'passwd_enc_type')) {
            $add_usrfields .= ' , usr_data.passwd_enc_type, usr_data.passwd_salt ';
        }
        $q = "SELECT usr_data.usr_id, usr_data.passwd $add_usrfields " .
            "FROM usr_data " .
            "LEFT JOIN rbac_ua ON rbac_ua.usr_id=usr_data.usr_id " .
            "WHERE rbac_ua.rol_id = " . $this->client->db->quote((int) $system_role_id, 'integer') . " " .
            "AND usr_data.login=" . $this->client->db->quote($a_auth_data["username"], 'text');
        $r = $this->client->db->query($q);
        if (!$this->client->db->numRows($r)) {
            $this->error = 'login_invalid';
            return false;
        }

        $data = $this->client->db->fetchAssoc($r);

        global $ilClientIniFile;

        $ilClientIniFile = $this->client->ini;

        require_once 'Services/User/classes/class.ilUserPasswordManager.php';
        $crypt_type = ilUserPasswordManager::getInstance()->getEncoderName();
        if (strlen($add_usrfields) && ilUserPasswordManager::getInstance()->isEncodingTypeSupported($crypt_type)) {
            require_once 'setup/classes/class.ilObjSetupUser.php';
            $user = new ilObjSetupUser();
            $user->setPasswd($data['passwd'], IL_PASSWD_CRYPTED);
            $user->setPasswordEncodingType($data['passwd_enc_type']);
            $user->setPasswordSalt($data['passwd_salt']);

            $password_valid = ilUserPasswordManager::getInstance()->verifyPassword($user, $a_auth_data['password']);
        } else {
            $password_valid = $data['passwd'] == md5($a_auth_data['password']);
        }

        if ($password_valid) {
            // all checks passed -> user valid
            $_SESSION['auth'] = true;
            $_SESSION['auth_path'] = ILIAS_HTTP_PATH;
            $_SESSION['access_mode'] = 'client';
            $_SESSION['ClientId'] = $this->client->getId();
            return true;
        } else {
            $this->error = 'login_invalid';
            return false;
        }
    }

    /**
     * Process setup admin login
     * @param  string $raw
     * @return bool A boolean status, whether or not the authentication was successful
     * @throws \ilUserException
     */
    public function loginAsAdmin(string $raw) : bool
    {
        $passwordReHashCallback = function ($raw) {
            $this->storeMasterPassword($raw);
        };

        if ($this->passwordManager->verifyPassword($this->getMasterPassword(), $raw, $passwordReHashCallback)) {
            $_SESSION['auth'] = true;
            $_SESSION['auth_path'] = ILIAS_HTTP_PATH;
            $_SESSION['access_mode'] = 'admin';
            return true;
        }

        return false;
    }

    /**
    * creates a client object in $this->client
    * @param	string	client id
    * @return	boolean
    */
    public function newClient($a_client_id = 0)
    {
        if (!$this->isInstalled()) {
            return false;
        }

        $this->client = new ilClient($a_client_id, $this->db_connections);

        if (!$this->client->init()) {
            //echo "<br>noclientinit";
            $this->error = get_class($this) . ": " . $this->client->getError();
            $_SESSION["ClientId"] = "";
            return false;
        }

        $_SESSION["ClientId"] = $a_client_id;

        return true;
    }

    /**
    * coumpute client status
    * @param	string	client id
    * @return	array	status information
    */
    public function getStatus($client = 0)
    {
        if (!is_object($client)) {
            if ($this->ini_client_exists) {
                $client = &$this->client;
            } else {
                $client = new ilClient(0, $this->db_connections);
            }
        }

        $status = array();
        $status["ini"] = $this->checkClientIni($client);		// check this one
        $status["db"] = $this->checkClientDatabase($client);
        if ($status["db"]["status"] === false and $status["db"]["update"] !== true) {
            //$status["sess"]["status"] = false;
            //$status["sess"]["comment"] = $status["db"]["comment"];
            $status["lang"]["status"] = false;
            $status["lang"]["comment"] = $status["db"]["comment"];
            $status["contact"]["status"] = false;
            $status["contact"]["comment"] = $status["db"]["comment"];

            $status["proxy"]["status"] = false;
            $status["proxy"]["comment"] = $status["db"]["comment"];

            $status["nic"]["status"] = false;
            $status["nic"]["comment"] = $status["db"]["comment"];
        } else {
            //$status["sess"] = $this->checkClientSessionSettings($client);
            $status["lang"] = $this->checkClientLanguages($client);
            $status["contact"] = $this->checkClientContact($client);
            $status["proxy"] = $this->checkClientProxySettings($client);
            $status["nic"] = $this->checkClientNIC($client);
            $status["finish"] = $this->checkFinish($client);
            $status["access"] = $this->checkAccess($client);
        }

        //return value
        return $status;
    }

    /**
    * check if client setup was finished
    * @param	object	client
    * @return	boolean
    */
    public function checkFinish(&$client)
    {
        if ($client->getSetting("setup_ok")) {
            $arr["status"] = true;
        //$arr["comment"] = $this->lng->txt("setup_finished");
        } else {
            $arr["status"] = false;
            $arr["comment"] = $this->lng->txt("setup_not_finished");
        }

        return $arr;
    }

    /**
    * check client access status
    * @param	object	client
    * @return	boolean
    */
    public function checkAccess(&$client)
    {
        if ($client->ini->readVariable("client", "access") == "1") {
            $arr["status"] = true;
            $arr["comment"] = $this->lng->txt("online");
        } else {
            $arr["status"] = false;
            $arr["comment"] = $this->lng->txt("disabled");
        }

        return $arr;
    }

    /**
    * check client ini status
    * @param	object	client
    * @return	boolean
    */
    public function checkClientIni(&$client)
    {
        if (!$arr["status"] = $client->init()) {
            $arr["comment"] = $client->getError();
        } else {
            //$arr["comment"] = "dir: /".ILIAS_WEB_DIR."/".$client->getId();
        }

        return $arr;
    }


    /**
     * @param \ilClient $client
     * @return array
     */
    public function checkClientDatabase(ilClient $client)
    {
        $arr = array();
        $client->provideGlobalDB();
        if (!$arr["status"] = $client->db_exists) {
            $arr["comment"] = $this->lng->txt("no_database");

            return $arr;
        }

        if (!$arr["status"] = $client->db_installed) {
            $arr["comment"] = $this->lng->txt("db_not_installed");

            return $arr;
        }
        // TODO: move this to client class!!
        $client->setup_ok = (bool) $client->getSetting("setup_ok");

        include_once "./Services/Database/classes/class.ilDBUpdate.php";
        $this->lng->setDbHandler($client->db);
        $dbupdate = new ilDBUpdate($client->db);

        if (!$arr["status"] = $dbupdate->getDBVersionStatus()) {
            $arr["comment"] = $this->lng->txt("db_needs_update");
            $arr["update"] = true;

            return $arr;
        } else {
            if ($dbupdate->hotfixAvailable()) {
                $arr["status"] = false;
                $arr["comment"] = $this->lng->txt("hotfix_available");
                $arr["update"] = true;

                return $arr;
            } else {
                if ($dbupdate->customUpdatesAvailable()) {
                    $arr["status"] = false;
                    $arr["comment"] = $this->lng->txt("custom_updates_available");
                    $arr["update"] = true;

                    return $arr;
                }
            }
        }

        // check control information
        global $ilDB;
        $cset = $ilDB->query("SELECT count(*) as cnt FROM ctrl_calls");
        $crec = $ilDB->fetchAssoc($cset);
        $client->revokeGlobalDB();
        if ($crec["cnt"] == 0) {
            $arr["status"] = false;
            $arr["comment"] = $this->lng->txt("db_control_structure_missing");
            $arr["update"] = true;

            return $arr;
        }

        return $arr;
    }

    /**
    * check client session config status
    * @param    object    client
    * @return    boolean
    */
    public function checkClientSessionSettings(&$client, $a_as_bool = false)
    {
        require_once('Services/Authentication/classes/class.ilSessionControl.php');

        global $ilDB;
        $db = $ilDB;

        $fields = ilSessionControl::getSettingFields();

        $query = "SELECT keyword, value FROM settings WHERE " . $db->in('keyword', $fields, false, 'text');
        $res = $db->query($query);

        $rows = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            if ($row['value'] != '') {
                $rows[] = $row;
            } else {
                break;
            }
        }

        if (count($rows) != count($fields)) {
            if ($a_as_bool) {
                return false;
            }
            $arr["status"] = false;
            $arr["comment"] = $this->lng->txt("session_management_not_configured");
        } else {
            if ($a_as_bool) {
                return true;
            }
            $arr["status"] = true;
            $arr["comment"] = $this->lng->txt("session_management_configured");
        }

        return $arr;
    }


    /**
     * @param \ilClient $client
     * @return array
     */
    public function checkClientProxySettings(ilClient $client)
    {
        $client->provideGlobalDB();
        global $ilDB;
        $arr = array();
        $fields = array( 'proxy_status', 'proxy_host', 'proxy_port' );

        $query = "SELECT keyword, value FROM settings WHERE " . $ilDB->in('keyword', $fields, false, 'text');
        $res = $ilDB->query($query);

        $proxy_settings = array();
        $already_saved = false;
        while ($row = $ilDB->fetchAssoc($res)) {
            $already_saved = true;
            $proxy_settings[$row['keyword']] = $row['value'];
        }

        if (!$already_saved) {
            $arr["status"] = false;
            $arr["comment"] = $this->lng->txt("proxy");
            $arr["text"] = $this->lng->txt("proxy");
        } else {
            if ((bool) $proxy_settings["proxy_status"] == false) {
                $arr["status"] = true;
                $arr["comment"] = $this->lng->txt("proxy_disabled");
                $arr["text"] = $this->lng->txt("proxy_disabled");
            } else {
                $arr["status"] = true;
                $arr["comment"] = $this->lng->txt("proxy_activated_configurated");
                $arr["text"] = $this->lng->txt("proxy_activated_configurated");
            }
        }

        return $arr;
    }


    /**
     * @param \ilClient $client
     * @return array
     */
    public function checkClientLanguages(ilClient $client)
    {
        $client->provideGlobalDB();
        $installed_langs = $this->lng->getInstalledLanguages();

        $count = count($installed_langs);
        $arr = array();
        if ($count < 1) {
            $arr["status"] = false;
            $arr["comment"] = $this->lng->txt("lang_none_installed");
        } else {
            $arr["status"] = true;
            //$arr["comment"] = $count." ".$this->lng->txt("languages_installed");
        }
        $client->revokeGlobalDB();
        return $arr;
    }

    /**
    * check client contact data status
    * @param	object	client
    * @return	boolean
    */
    public function checkClientContact(&$client)
    {
        $arr["status"] = true;
        //$arr["comment"] = $this->lng->txt("filled_out");

        $settings = $client->getAllSettings();
        $client_name = $client->getName();

        // check required fields
        if (empty($settings["admin_firstname"]) or empty($settings["admin_lastname"]) or
            empty($settings["admin_email"]) or empty($client_name)) {
            $arr["status"] = false;
            $arr["comment"] = $this->lng->txt("missing_data");
        }

        // admin email
        if (!ilUtil::is_email($settings["admin_email"]) and $arr["status"] != false) {
            $arr["status"] = false;
            $arr["comment"] = $this->lng->txt("email_not_valid");
        }

        return $arr;
    }

    /**
    * check client nic status
    * @param	object	client
    * @return	boolean
    */
    public function checkClientNIC(&$client)
    {
        $settings = $client->getAllSettings();

        if (!isset($settings["nic_enabled"])) {
            $arr["status"] = false;
            $arr["comment"] = $this->lng->txt("nic_not_disabled");
            return $arr;
        }

        $arr["status"] = true;

        if ($settings["nic_enabled"] == "-1") {
            $arr["comment"] = $this->lng->txt("nic_reg_failed");
            return $arr;
        }

        if (!$settings["nic_enabled"]) {
            $arr["comment"] = $this->lng->txt("nic_reg_disabled");
        } else {
            $arr["comment"] = $this->lng->txt("nic_reg_enabled");
            if ($settings["inst_id"] <= 0) {
                $arr["status"] = false;
            }
        }

        return $arr;
    }

    /**
    * check if client's db is installed
    * @return	boolean
    */
    public function isInstalled()
    {
        return $this->ini_ilias_exists;
    }

    /**
    * check if current user is authenticated
    * @return	boolean
    */
    public function isAuthenticated()
    {
        return $this->auth;
    }

    /**
    * check if current user is admin
    * @return	boolean
    */
    public function isAdmin()
    {
        return ($this->access_mode == "admin") ? true : false;
    }

    /**
    * saves intial settings
    * @param	array	form data
    * @return	boolean
    */
    public function saveMasterSetup($a_formdata)
    {
        $datadir_path = preg_replace("/\\\\/", "/", ilFile::deleteTrailingSlash(ilUtil::stripSlashes($a_formdata["datadir_path"])));

        if ($a_formdata["chk_datadir_path"] == 1) {	// mode create dir
            if (!ilUtil::makeDir($datadir_path)) {
                $this->error = "create_datadir_failed";
                return false;
            }
        }

        // create webspace dir if it does not exist
        if (!@file_exists(ILIAS_ABSOLUTE_PATH . "/" . $this->ini->readVariable("clients", "path")) and !@is_dir(ILIAS_ABSOLUTE_PATH . "/" . $this->ini->readVariable("clients", "path"))) {
            if (!ilUtil::makeDir(ILIAS_ABSOLUTE_PATH . "/" . $this->ini->readVariable("clients", "path"))) {
                $this->error = "create_webdir_failed";
                return false;
            }
        }

        $form_log_path = preg_replace("/\\\\/", "/", ilFile::deleteTrailingSlash(ilUtil::stripSlashes($a_formdata["log_path"])));
        $log_path = substr($form_log_path, 0, strrpos($form_log_path, "/"));
        $log_file = substr($form_log_path, strlen($log_path) + 1);
        $error_log_path = preg_replace("/\\\\/", "/", ilFile::deleteTrailingSlash(ilUtil::stripSlashes($a_formdata["error_log_path"])));

        $this->ini->setVariable("server", "http_path", ILIAS_HTTP_PATH);
        $this->ini->setVariable("server", "absolute_path", ILIAS_ABSOLUTE_PATH);
        $this->ini->setVariable("server", "timezone", preg_replace("/\\\\/", "/", ilUtil::stripSlashes($a_formdata["time_zone"])));
        $this->ini->setVariable("clients", "datadir", $datadir_path);
        $this->ini->setVariable("tools", "convert", preg_replace("/\\\\/", "/", ilUtil::stripSlashes($a_formdata["convert_path"])));
        $this->ini->setVariable("tools", "zip", preg_replace("/\\\\/", "/", ilUtil::stripSlashes($a_formdata["zip_path"])));
        $this->ini->setVariable("tools", "unzip", preg_replace("/\\\\/", "/", ilUtil::stripSlashes($a_formdata["unzip_path"])));
        $this->ini->setVariable("tools", "ghostscript", preg_replace("/\\\\/", "/", ilUtil::stripSlashes($a_formdata["ghostscript_path"])));
        $this->ini->setVariable("tools", "java", preg_replace("/\\\\/", "/", ilUtil::stripSlashes($a_formdata["java_path"])));
        //$this->ini->setVariable("tools", "mkisofs", preg_replace("/\\\\/","/",ilUtil::stripSlashes($a_formdata["mkisofs_path"])));
        $this->ini->setVariable("tools", "ffmpeg", preg_replace("/\\\\/", "/", ilUtil::stripSlashes($a_formdata["ffmpeg_path"])));
        $this->ini->setVariable("tools", "latex", ilUtil::stripSlashes($a_formdata["latex_url"]));
        $this->ini->setVariable("tools", "vscantype", preg_replace("/\\\\/", "/", ilUtil::stripSlashes($a_formdata["vscanner_type"])));
        $this->ini->setVariable("tools", "scancommand", preg_replace("/\\\\/", "/", ilUtil::stripSlashes($a_formdata["scan_command"])));
        $this->ini->setVariable("tools", "cleancommand", preg_replace("/\\\\/", "/", ilUtil::stripSlashes($a_formdata["clean_command"])));
        $this->ini->setVariable("tools", "enable_system_styles_management", preg_replace("/\\\\/", "/", ilUtil::stripSlashes($a_formdata["enable_system_styles_management"])));
        $this->ini->setVariable("tools", "lessc", preg_replace("/\\\\/", "/", ilUtil::stripSlashes($a_formdata["lessc_path"])));
        $this->ini->setVariable("tools", "phantomjs", preg_replace("/\\\\/", "/", ilUtil::stripSlashes($a_formdata["phantomjs_path"])));

        $this->ini->setVariable('setup', 'pass', $this->passwordManager->encodePassword($a_formdata['setup_pass']));
        $this->ini->setVariable("log", "path", $log_path);
        $this->ini->setVariable("log", "file", $log_file);
        $this->ini->setVariable("log", "enabled", ($a_formdata["chk_log_status"]) ? "0" : 1);
        $this->ini->setVariable("log", "error_path", $error_log_path);

        $this->ini->setVariable("https", "auto_https_detect_enabled", ($a_formdata["auto_https_detect_enabled"]) ? 1 : 0);
        $this->ini->setVariable("https", "auto_https_detect_header_name", $a_formdata["auto_https_detect_header_name"]);
        $this->ini->setVariable("https", "auto_https_detect_header_value", $a_formdata["auto_https_detect_header_value"]);

        if (!$this->ini->write()) {
            $this->error = get_class($this) . ": " . $this->ini->getError();
            return false;
        }

        // everything is fine. so we authenticate the user and set access mode to 'admin'
        $_SESSION["auth"] = true;
        $_SESSION["auth_path"] = ILIAS_HTTP_PATH;
        $_SESSION["access_mode"] = "admin";

        return true;
    }

    /**
    * updates settings
    * @param	array	form data
    * @return	boolean
    */
    public function updateMasterSettings($a_formdata)
    {
        $convert_path = preg_replace("/\\\\/", "/", ilUtil::stripSlashes($a_formdata["convert_path"]));
        $zip_path = preg_replace("/\\\\/", "/", ilUtil::stripSlashes($a_formdata["zip_path"]));
        $unzip_path = preg_replace("/\\\\/", "/", ilUtil::stripSlashes($a_formdata["unzip_path"]));
        $ghostscript_path = preg_replace("/\\\\/", "/", ilUtil::stripSlashes($a_formdata["ghostscript_path"]));
        $java_path = preg_replace("/\\\\/", "/", ilUtil::stripSlashes($a_formdata["java_path"]));
        //$mkisofs_path = preg_replace("/\\\\/","/",ilUtil::stripSlashes($a_formdata["mkisofs_path"]));
        $ffmpeg_path = preg_replace("/\\\\/", "/", ilUtil::stripSlashes($a_formdata["ffmpeg_path"]));
        $latex_url = ilUtil::stripSlashes($a_formdata["latex_url"]);
        $fop_path = preg_replace("/\\\\/", "/", ilUtil::stripSlashes($a_formdata["fop_path"]));
        $scan_type = preg_replace("/\\\\/", "/", ilUtil::stripSlashes($a_formdata["vscanner_type"]));
        $scan_command = preg_replace("/\\\\/", "/", ilUtil::stripSlashes($a_formdata["scan_command"]));
        $clean_command = preg_replace("/\\\\/", "/", ilUtil::stripSlashes($a_formdata["clean_command"]));
        $enable_system_styles_management = preg_replace("/\\\\/", "/", ilUtil::stripSlashes($a_formdata["enable_system_styles_management"]));
        $lessc_path = preg_replace("/\\\\/", "/", ilUtil::stripSlashes($a_formdata["lessc_path"]));
        $phantomjs_path = preg_replace("/\\\\/", "/", ilUtil::stripSlashes($a_formdata["phantomjs_path"]));

        $this->ini->setVariable("tools", "convert", $convert_path);
        $this->ini->setVariable("tools", "zip", $zip_path);
        $this->ini->setVariable("tools", "unzip", $unzip_path);
        $this->ini->setVariable("tools", "ghostscript", $ghostscript_path);
        $this->ini->setVariable("tools", "java", $java_path);
        //$this->ini->setVariable("tools", "mkisofs", $mkisofs_path);
        $this->ini->setVariable("tools", "ffmpeg", $ffmpeg_path);
        $this->ini->setVariable("tools", "latex", $latex_url);
        $this->ini->setVariable("tools", "fop", $fop_path);
        $this->ini->setVariable("tools", "vscantype", $scan_type);
        $this->ini->setVariable("tools", "scancommand", $scan_command);
        $this->ini->setVariable("tools", "cleancommand", $clean_command);
        $this->ini->setVariable("tools", "lessc", $lessc_path);
        $this->ini->setVariable("tools", "enable_system_styles_management", $enable_system_styles_management);
        $this->ini->setVariable("tools", "phantomjs", $phantomjs_path);

        $form_log_path = preg_replace("/\\\\/", "/", ilFile::deleteTrailingSlash(ilUtil::stripSlashes($a_formdata["log_path"])));
        $log_path = substr($form_log_path, 0, strrpos($form_log_path, "/"));
        $log_file = substr($form_log_path, strlen($log_path) + 1);

        $error_log_path = preg_replace("/\\\\/", "/", ilFile::deleteTrailingSlash(ilUtil::stripSlashes($a_formdata["error_log_path"])));

        $this->ini->setVariable("log", "path", $log_path);
        $this->ini->setVariable("log", "file", $log_file);
        $this->ini->setVariable("log", "enabled", ($a_formdata["chk_log_status"]) ? "0" : 1);
        $this->ini->setVariable("log", "error_path", $error_log_path);
        $this->ini->setVariable("server", "timezone", preg_replace("/\\\\/", "/", ilUtil::stripSlashes($a_formdata["time_zone"])));

        $this->ini->setVariable("https", "auto_https_detect_enabled", ($a_formdata["auto_https_detect_enabled"]) ? 1 : 0);
        $this->ini->setVariable("https", "auto_https_detect_header_name", $a_formdata["auto_https_detect_header_name"]);
        $this->ini->setVariable("https", "auto_https_detect_header_value", $a_formdata["auto_https_detect_header_value"]);

        if (!$this->ini->write()) {
            $this->error = get_class($this) . ": " . $this->ini->getError();
            return false;
        }

        return true;
    }

    /**
    * check pathes to 3rd party software
    * @param	array	form data
    * @return	boolean
    */
    public function checkToolsSetup($a_formdata)
    {
        // convert path
        if (!isset($a_formdata["chk_convert_path"])) {
            // convert backslashes to forwardslashes
            $convert_path = preg_replace("/\\\\/", "/", ilUtil::stripSlashes($a_formdata["convert_path"]));

            if (($err = $this->testConvert($convert_path)) != "") {
                $this->error = $err;
                return false;
            }
        }

        // zip path
        if (!isset($a_formdata["chk_zip_path"])) {
            // convert backslashes to forwardslashes
            $zip_path = preg_replace("/\\\\/", "/", ilUtil::stripSlashes($a_formdata["zip_path"]));

            if (empty($zip_path)) {
                $this->error = "no_path_zip";
                return false;
            }

            if (!$this->testZip($zip_path)) {
                $this->error = "check_failed_zip";
                return false;
            }
        }

        // unzip path
        if (!isset($a_formdata["chk_unzip_path"])) {
            // convert backslashes to forwardslashes
            $unzip_path = preg_replace("/\\\\/", "/", ilUtil::stripSlashes($a_formdata["unzip_path"]));

            if (empty($unzip_path)) {
                $this->error = "no_path_unzip";
                return false;
            }

            if (!$this->testUnzip($unzip_path)) {
                $this->error = "check_failed_unzip";
                return false;
            }
        }

        // ghostscript path
        if (!isset($a_formdata["chk_ghostscript_path"])) {
            // convert backslashes to forwardslashes
            $ghostscript_path = preg_replace("/\\\\/", "/", ilUtil::stripSlashes($a_formdata["ghostscript_path"]));

            if (($err = $this->testGhostscript($ghostscript_path)) != "") {
                $this->error = $err;
                return false;
            }
        }

        // java path
        if (!isset($a_formdata["chk_java_path"])) {
            // convert backslashes to forwardslashes
            $java_path = preg_replace("/\\\\/", "/", ilUtil::stripSlashes($a_formdata["java_path"]));

            if (empty($java_path)) {
                $this->error = "no_path_java";
                return false;
            }

            if (!$this->testJava($java_path)) {
                $this->error = "check_failed_java";
                return false;
            }
        }

        /*if (!isset($a_formdata["chk_mkisofs_path"]))
        {
            // convert backslashes to forwardslashes
            $mkisofs_path = preg_replace("/\\\\/","/",ilUtil::stripSlashes($a_formdata["mkisofs_path"]));

            if (empty($mkisofs_path))
            {
                $this->error = "no_path_mkisofs";
                return false;
            }
        }*/

        if (!isset($a_formdata["chk_ffmpeg_path"])) {
            // convert backslashes to forwardslashes
            $ffmpeg_path = preg_replace("/\\\\/", "/", ilUtil::stripSlashes($a_formdata["ffmpeg_path"]));

            if (empty($ffmpeg_path)) {
                $this->error = "no_path_ffmpeg";
                return false;
            }

            if (!$this->testFFMpeg($ffmpeg_path)) {
                $this->error = "check_failed_ffmpeg";
                return false;
            }
        }

        // latex  url
        if (!isset($a_formdata["chk_latex_url"])) {
            $latex_url = ilUtil::stripSlashes($a_formdata["latex_url"]);
            if (empty($latex_url)) {
                $this->error = "no_latex_url";
                return false;
            }

            if (!$this->testLatex($latex_url)) {
                $this->error = "check_failed_latex";
                return false;
            }
        }

        return true;
    }

    /**
    * check datadir path
    * @param	array	form data
    * @return	boolean
    */
    public function checkDataDirSetup($a_formdata)
    {
        // remove trailing slash & convert backslashes to forwardslashes
        $datadir_path = preg_replace("/\\\\/", "/", ilFile::deleteTrailingSlash(ilUtil::stripSlashes($a_formdata["datadir_path"])));

        if (empty($datadir_path)) {
            $this->error = "no_path_datadir";
            return false;
        }

        $webspace_dir = ILIAS_ABSOLUTE_PATH . "/data";

        // datadir may not point to webspace dir or to any place under webspace_dir
        if (strpos($datadir_path, $webspace_dir) !== false) {
            $this->error = "datadir_webspacedir_match";
            return false;
        }

        // create dir
        if ($a_formdata["chk_datadir_path"] == 1) {
            $dir_to_create = substr(strrchr($datadir_path, "/"), 1);
            $dir_to_check = substr($datadir_path, 0, -strlen($dir_to_create) - 1);

            if ($this->isDirectoryInOther($dir_to_create, ILIAS_ABSOLUTE_PATH)) {
                $this->error = "cannot_create_datadir_inside_webdir";
                return false;
            }

            if (is_writable($datadir_path)) {
                $this->error = "dir_exists_create";
                return false;
            }

            if (!is_writable($dir_to_check)) {
                $this->error = "cannot_create_datadir_no_write_access";
                return false;
            }
        } else {	// check set target dir
            if ($this->isDirectoryInOther($datadir_path, ILIAS_ABSOLUTE_PATH)) {
                $this->error = "cannot_create_datadir_inside_webdir";
                return false;
            }

            if (!is_writable($datadir_path)) {
                $this->error = "cannot_create_datadir_no_write_access";
                return false;
            }
        }

        return true;
    }

    /**
    * check setup password
    * @param	array	form data
    * @return	boolean
    */
    public function checkPasswordSetup($a_formdata)
    {
        if (!$a_formdata["setup_pass"]) {
            $this->error = "no_setup_pass_given";
            return false;
        }

        if ($a_formdata["setup_pass"] != $a_formdata["setup_pass2"]) {
            $this->error = "pass_does_not_match";
            return false;
        }

        return true;
    }

    /**
    * check log path
    * @param	array	form data
    * @return	boolean
    */
    public function checkLogSetup($a_formdata)
    {
        // log path
        if (!$a_formdata["chk_log_status"]) {
            // remove trailing slash & convert backslashes to forwardslashes
            $log_path = preg_replace("/\\\\/", "/", ilFile::deleteTrailingSlash(ilUtil::stripSlashes($a_formdata["log_path"])));

            if (empty($log_path)) {
                $this->error = "no_path_log";
                return false;
            }
            
            if (is_dir($log_path)) {
                $this->error = 'could_not_create_logfile';
                return false;
            }

            if ($this->isDirectoryInOther($log_path, ILIAS_ABSOLUTE_PATH)) {
                $this->error = "cannot_create_logdir_inside_webdir";
                return false;
            }

            if (!@touch($log_path)) {
                $this->error = "could_not_create_logfile";
                return false;
            }
        }

        return true;
    }

    /**
     * check error log path
     *
     * @param	string	$error_log_path		path to save error log files
     *
     * @return	boolean
     */
    public function checkErrorLogSetup($error_log_path)
    {
        // remove trailing slash & convert backslashes to forwardslashes
        $clean_error_log_path = preg_replace("/\\\\/", "/", ilFile::deleteTrailingSlash(ilUtil::stripSlashes($error_log_path)));

        if (!empty($clean_error_log_path)) {
            if (!ilUtil::makeDirParents($clean_error_log_path)) {
                $this->error = "could_not_create_error_directory";
                return false;
            }
        }

        return true;
    }

    /**
    * get Error message
    * @return	string	error message
    */
    public function getError()
    {
        if (empty($this->error)) {
            return false;
        }

        $error = $this->error;
        $this->error = "";

        return $error;
    }

    /**
    * destructor
    *
    * @return boolean
    */
    public function _ilSetup()
    {
        //if ($this->ini->readVariable("db","type") != "")
        //{
        //	$this->db->disconnect();
        //}
        return true;
    }

    /**
    * Check convert program
    *
    * @param	string		convert path
    * @return	boolean		true -> OK | false -> not OK
    */
    public function testConvert($a_convert_path)
    {
        if (trim($a_convert_path) == "") {
            return "no_path_convert";
        }
        if (!is_file($a_convert_path)) {
            return "check_failed_convert";
        }

        return "";
    }

    /**
     * Check ghostscript program
     *
     * @param	string		ghostscript path
     * @return	boolean		true -> OK | false -> not OK
     */
    public function testGhostscript($a_ghostscript_path)
    {
        // ghostscript is optional, so empty path is ok
        if (trim($a_ghostscript_path) == "") {
            return "";
        }
        if (!is_file($a_ghostscript_path)) {
            return "check_failed_ghostscript";
        }

        return "";
    }

    /**
    * Check JVM
    *
    * @param	string		java path
    * @return	boolean		true -> OK | false -> not OK
    */
    public function testJava($a_java_path)
    {
        // java is optional, so empty path is ok
        if (trim($a_java_path) == "") {
            return "";
        }

        if (!is_file($a_java_path)) {
            return "check_failed_java";
        }

        return "";
        /*
                exec($a_java_path, $out, $back);

                unset($out);

                return ($back != 1) ? false : true;
        */
    }

    /**
    * Check latex cgi script
    *
    * @param	string		latex cgi url
    * @return	boolean		true -> OK | false -> not OK
    */
    public function testLatex($a_latex_url)
    {
        // latex is optional, so empty path is ok
        if (trim($a_latex_url) == "") {
            return "";
        }

        // open the URL
        include_once "./setup/classes/class.ilHttpRequest.php";
        $http = new ilHttpRequest(ilUtil::stripSlashes($a_latex_url) . "?x_0");
        $result = @$http->downloadToString();
        if ((strpos((substr($result, 0, 5)), "PNG") !== false) || (strpos((substr($result, 0, 5)), "GIF") !== false)) {
            return "";
        } else {
            return "check_failed_latex";
            ;
        }
    }

    /**
    * Check zip program
    *
    * @param	string		zip path
    * @return	boolean		true -> OK | false -> not OK
    */
    public function testZip($a_zip_path)
    {
        if (trim($a_zip_path) == "") {
            return "no_path_zip";
        }
        if (!is_file($a_zip_path)) {
            return "check_failed_zip";
        }

        return "";
        /*
                // create test file and run zip
                $fp = fopen(ILIAS_ABSOLUTE_PATH."/test.dat", "w");

                fwrite($fp, "test");
                fclose($fp);

                if (file_exists(ILIAS_ABSOLUTE_PATH."/test.dat"))
                {
                    $curDir = getcwd();
                    chdir(ILIAS_ABSOLUTE_PATH);

                    $zipCmd = $a_zip_path." -m zip_test_file.zip test.dat";

                    exec($zipCmd);

                    chdir($curDir);

                }

                // check wether zip generated test file or not
                if (file_exists(ILIAS_ABSOLUTE_PATH."/zip_test_file.zip"))
                {
                    unlink(ILIAS_ABSOLUTE_PATH."/zip_test_file.zip");
                    return true;
                }
                else
                {
                    unlink(ILIAS_ABSOLUTE_PATH."/test.dat");
                    return false;
                }
        */
    }


    /**
    * Check unzip program
    *
    * @param	string		unzip_path
    * @return	boolean		true -> OK | false -> not OK
    */
    public function testUnzip($a_unzip_path)
    {
        if (trim($a_unzip_path) == "") {
            return "no_path_unzip";
        }
        if (!is_file($a_unzip_path)) {
            return "check_failed_unzip";
        }

        return "";
        /*
                $curDir = getcwd();

                chdir(ILIAS_ABSOLUTE_PATH);

                if (file_exists(ILIAS_ABSOLUTE_PATH."/unzip_test_file.zip"))
                {
                    $unzipCmd = $a_unzip_path." unzip_test_file.zip";
                    exec($unzipCmd);
                }

                chdir($curDir);

                // check wether unzip extracted the test file or not
                if (file_exists(ILIAS_ABSOLUTE_PATH."/unzip_test_file.txt"))
                {
                    unlink(ILIAS_ABSOLUTE_PATH."/unzip_test_file.txt");

                    return true;
                }
                else
                {
                    return false;
                }
        */
    }

    /**
    * unzip file
    *
    * @param	string	$a_file		full path/filename
    * @param	boolean	$overwrite	pass true to overwrite existing files
    */
    public function unzip($a_file, $overwrite = false)
    {
        //global $ilias;

        $pathinfo = pathinfo($a_file);
        $dir = $pathinfo["dirname"];
        $file = $pathinfo["basename"];

        // unzip
        $cdir = getcwd();
        chdir($dir);
        $unzip = $this->ini->readVariable("tools", "unzip");
        $unzipcmd = $unzip . " -Z -1 " . ilUtil::escapeShellArg($file);
        exec($unzipcmd, $arr);
        $zdirs = array();

        foreach ($arr as $line) {
            if (is_int(strpos($line, "/"))) {
                $zdir = substr($line, 0, strrpos($line, "/"));
                $nr = substr_count($zdir, "/");
                //echo $zdir." ".$nr."<br>";
                while ($zdir != "") {
                    $nr = substr_count($zdir, "/");
                    $zdirs[$zdir] = $nr;				// collect directories
                    //echo $dir." ".$nr."<br>";
                    $zdir = substr($zdir, 0, strrpos($zdir, "/"));
                }
            }
        }

        asort($zdirs);

        foreach ($zdirs as $zdir => $nr) {				// create directories
            ilUtil::createDirectory($zdir);
        }

        // real unzip
        if ($overvwrite) {
            $unzipcmd = $unzip . " " . ilUtil::escapeShellArg($file);
        } else {
            $unzipcmd = $unzip . " -o " . ilUtil::escapeShellArg($file);
        }
        exec($unzipcmd);

        chdir($cdir);
    }

    /**
     * saves session settings to db
     *
     * @param array $session_settings
     */
    public function setSessionSettings($session_settings)
    {
        require_once('Services/Authentication/classes/class.ilSessionControl.php');

        $db = $this->client->getDB();

        $setting_fields = ilSessionControl::getSettingFields();

        $i = 0;
        foreach ($setting_fields as $field) {
            if (isset($session_settings[$field])) {
                $query = "SELECT keyword FROM settings WHERE module = %s AND keyword = %s";
                $res = $db->queryF(
                    $query,
                    array('text', 'text'),
                    array('common', $field)
                );

                $row = array();
                while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
                    break;
                }

                if (count($row) > 0) {
                    $db->update(
                        'settings',
                        array(
                            'value' => array('text', $session_settings[$field])
                        ),
                        array(
                            'module' => array('text', 'common'),
                            'keyword' => array('text', $field)
                        )
                    );
                } else {
                    $db->insert(
                        'settings',
                        array(
                            'module' => array('text', 'common'),
                            'keyword' => array('text', $field),
                            'value' => array('text', $session_settings[$field])
                        )
                    );
                }

                $i++;
            }
        }

        if ($i < 4) {
            $message = $this->lng->txt("session_settings_not_saved");
        } else {
            $message = $this->lng->txt("settings_saved");
        }

        ilUtil::sendInfo($message);
    }

    /**
     * reads session settings from db
     *
     * @return array session_settings
     */
    public function getSessionSettings()
    {
        require_once('Services/Authentication/classes/class.ilSessionControl.php');

        $db = $this->client->getDB();

        $setting_fields = ilSessionControl::getSettingFields();

        $query = "SELECT * FROM settings WHERE module = %s " .
                "AND " . $db->in('keyword', $setting_fields, false, 'text');

        $res = $db->queryF($query, array('text'), array('common'));

        $session_settings = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $session_settings[$row['keyword']] = $row['value'];
        }

        foreach ($setting_fields as $field) {
            if (!isset($session_settings[$field])) {
                $value = 1;

                switch ($field) {
                    case 'session_max_count':

                        $value = ilSessionControl::DEFAULT_MAX_COUNT;
                        break;

                    case 'session_min_idle':

                        $value = ilSessionControl::DEFAULT_MIN_IDLE;
                        break;

                    case 'session_max_idle':

                        $value = ilSessionControl::DEFAULT_MAX_IDLE;
                        break;

                    case 'session_max_idle_after_first_request':

                        $value = ilSessionControl::DEFAULT_MAX_IDLE_AFTER_FIRST_REQUEST;
                        break;

                    case 'session_allow_client_maintenance':

                        $value = ilSessionControl::DEFAULT_ALLOW_CLIENT_MAINTENANCE;
                        break;
                }

                $session_settings[$field] = $value;
            }
        }

        return $session_settings;
    }

    /**
    * Clone source client into current client
    * @param	array	form data
    * @return	boolean
    */
    public function cloneFromSource($source_id)
    {
        // Getting source and targets
        $source = new ilClient($source_id, $this->db_connections);
        $source->init();
        $target = $this->client;

        // ************************************************
        // **  COPY FILES

        // Cleaning up datadir
        if (!ilUtil::delDir($target->getDataDir())) {
            $this->error = "Could not delete data dir $target->getDataDir()";
            //return false;
        }

        // Create empty datadir
        if (!ilUtil::makeDir($target->getDataDir())) {
            $this->error = "could_not_create_base_data_dir :" . $target->getDataDir();
            return false;
        }

        // Copying datadir
        if (!ilUtil::rCopy($source->getDataDir(), $target->getDataDir())) {
            $this->error = "clone_datadircopyfail";
            $target->ini->write();
            return false;
        }

        // Cleaning up Webspacedir
        if (!ilUtil::delDir($target->getWebspaceDir())) {
            $this->error = "Could not delete webspace dir $target->getWebspaceDir()";
            //return false;
        }

        // Create empty Webspacedir
        if (!ilUtil::makeDir($target->getWebspaceDir())) {
            $this->error = "could_not_create_base_webspace_dir :" . $target->getWebspaceDir();
            return false;
        }

        // Copying Webspacedir
        if (!ilUtil::rCopy($source->getWebspaceDir(), $target->getWebspaceDir())) {
            $this->error = "clone_websipacedircopyfail";
            $target->ini->write();
            return false;
        }

        // Restore ini file
        $target->ini->write();

        // ************************************************
        // **  COPY DATABASE

        $source->connect();
        if (!$source->db) {
            $this->error = "Source database connection failed.";
            return false;
        }

        $target->connect();
        if (!$target->db) {
            $this->error = "Target database connection failed.";
            return false;
        }

        $source->connect();
        $srcTables = $source->db->query("SHOW TABLES");
        $target->connect();

        // drop all tables of the target db
        $tarTables = $target->db->query("SHOW TABLES");
        foreach ($tarTables->fetchAll() as $cTable) {
            $target->db->query("DROP TABLE IF EXISTS " . $cTable[0]);
        }

        foreach ($srcTables->fetchAll() as $cTable) {
            $drop = $target->db->query("DROP TABLE IF EXISTS " . $cTable[0]);
            $create = $target->db->query("CREATE TABLE " . $cTable[0] . " LIKE " . $source->getDbName() . "." . $cTable[0]);
            if (!$create) {
                $error = true;
            }
            $insert = $target->db->query("INSERT INTO " . $cTable[0] . " SELECT * FROM " . $source->getDbName() . "." . $cTable[0]);
        }

        $target->db->query("UPDATE settings SET VALUE = " . $target->db->quote(0, "integer") . " WHERE keyword = " . $target->db->quote("inst_id", "text"));
        $target->db->query("UPDATE settings SET VALUE = " . $target->db->quote(0, "integer") . " WHERE keyword = " . $target->db->quote("nic_enabled", "text"));
        return true;
    }
    /**
     *
     * Print proxy settings
     *
     * @access	private
     *
     */
    public function printProxyStatus($client)
    {
        require_once './Services/Http/exceptions/class.ilProxyException.php';
        $settings = $client->getAllSettings();

        if ((bool) $settings['proxy_status'] == true) {
            try {
                $err_str = false;
                $wait_timeout = 100;

                $fp = @fsockopen($settings['proxy_host'], $settings['proxy_port'], $err_code, $err_str, $wait_timeout);

                if ($err_str) {
                    throw new ilProxyException($err_str);
                }

                fclose($fp);

                ilUtil::sendSuccess($this->lng->txt('proxy_connectable'));
            } catch (Exception $e) {
                ilUtil::sendFailure($this->lng->txt('proxy_not_connectable') . ": " . $e->getMessage());
            }
        }
    }

    public function saveProxySettings($proxy_settings)
    {
        $db = $this->client->getDB();
        $proxy_fields = array('proxy_status','proxy_host','proxy_port');

        foreach ($proxy_fields as $field) {
            if (isset($proxy_settings[$field])) {
                $query = "SELECT keyword FROM settings WHERE module = %s AND keyword = %s";
                $res = $db->queryF(
                    $query,
                    array('text', 'text'),
                    array('common', $field)
                );

                $row = array();
                while ($row = $db->fetchAssoc($res)) {
                    break;
                }

                if (is_array($row) && count($row) > 0) {
                    $db->update(
                        'settings',
                        array(
                            'value' => array('text', $proxy_settings[$field])
                        ),
                        array(
                            'module' => array('text', 'common'),
                            'keyword' => array('text', $field)
                        )
                    );
                } else {
                    $db->insert(
                        'settings',
                        array(
                            'module' => array('text', 'common'),
                            'keyword' => array('text', $field),
                            'value' => array('text', $proxy_settings[$field])
                        )
                    );
                }
            }
        }
    }

    /**
     * @return bool
     */
    public function hasOpCacheEnabled()
    {
        $ini_get = ini_get('opcache.enable');

        return ($ini_get === 1 or $ini_get === '1' or strtolower($ini_get) === 'on');
    }

    /**
     * Is valid client id
     *
     * @param
     * @return
     */
    public function isValidClientId($a_client_id)
    {
        if (!preg_match("/^[A-Za-z0-9]+$/", $a_client_id)) {
            return false;
        }
        return true;
    }

    /**
     * Checks if directory is subdirectory of other directory.
     *
     * @param	string	$directory
     * @param	string	$other_directory
     * @return	bool
     */
    protected function isDirectoryInOther($directory, $other_directory)
    {
        $other_directory = $other_directory . "/";

        return !(strpos($directory, $other_directory) !== 0);
    }
}
