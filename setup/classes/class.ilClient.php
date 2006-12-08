<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
* client management
*
* @author Sascha Hofmann <shofmann@databay.de> 
* @version $Id$
*
*/

class ilClient
{
	var $id;					// client_id (md5 hash)
	var $dir;					// directory name in ilias/clients/
	var $name;					// installation name
	var $db_exists = false;		// db exists?
	var $db_installed = false;	// db installed?

	var $client_defaults;		// default settings
	var $status;				// contains status infos about setup process (todo: move function to this class)
	var $setup_ok = false;		// if client setup was finished at least once, this is set to true
	var $nic_status;			// contains received data of ILIAS-NIC server when registering

	/**
	* Constructor
	* @param	string	client id
	*/
	function ilClient($a_client_id = 0)
	{
		if ($a_client_id)
		{
			$this->id = $a_client_id;
			$this->ini_file_path = ILIAS_ABSOLUTE_PATH."/".ILIAS_WEB_DIR."/".$this->getId()."/client.ini.php";
		}

		// set path default.ini
		$this->client_defaults = ILIAS_ABSOLUTE_PATH."/setup/client.master.ini.php";
	}
	
	/**
	* init client
	* load client.ini and set some constants
	* @return	boolean
	*/
	function init()
	{
		$this->ini = new ilIniFile($this->ini_file_path);
	
		// load defaults only if no client.ini was found
		if (!@file_exists($this->ini_file_path))
		{
			$this->ini->GROUPS = parse_ini_file($this->client_defaults,true);
			return false;
		}

		// read client.ini
		if (!$this->ini->read())
		{
			$this->error = get_class($this).": ".$this->ini->getError();
			return false;		
		}

		// only for ilias main
		define("CLIENT_WEB_DIR",ILIAS_ABSOLUTE_PATH."/".ILIAS_WEB_DIR."/".$this->getId());
		define("CLIENT_DATA_DIR",ILIAS_DATA_DIR."/".$this->getId());
		define ("DEVMODE",$this->ini->readVariable('system','DEVMODE'));
		define ("ROOT_FOLDER_ID",$this->ini->readVariable('system','ROOT_FOLDER_ID'));
		define ("SYSTEM_FOLDER_ID",$this->ini->readVariable('system','SYSTEM_FOLDER_ID'));
		define ("ROLE_FOLDER_ID",$this->ini->readVariable('system','ROLE_FOLDER_ID'));
		define ("ANONYMOUS_USER_ID",13);
		define ("ANONYMOUS_ROLE_ID",14);
		define ("SYSTEM_USER_ID",6);
		define ("SYSTEM_ROLE_ID",2);
		
		$this->db_exists = $this->connect();
		
		if ($this->db_exists)
		{
			$this->db_installed = $this->isInstalledDB($this->db);
		}
		
		return true;	
	}
	
	/**
	* get client id
	* @return	string	client id
	*/
	function getId()
	{
		return $this->id;
	}
	
	/**
	* set client id
	* @param	string	client id
	*/
	function setId($a_client_id)
	{
		$this->id = $a_client_id;
		$this->webspace_dir = ILIAS_ABSOLUTE_PATH."/".ILIAS_WEB_DIR."/".$this->id;
	}
	
	/**
	* get client name
	* @return	string	client name
	*/
	function getName()
	{
		return $this->ini->readVariable("client","name");
	}
	
	/**
	* set client name
	* @param	string	client name
	*/
	function setName($a_str)
	{
		$this->ini->setVariable("client","name",$a_str);
	}
	
	/**
	* get client description
	* @return	string	client description
	*/
	function getDescription()
	{
		return $this->ini->readVariable("client","description");
	}
	
	/**
	* set client description
	* @param	string	client description
	*/
	function setDescription($a_str)
	{
		$this->ini->setVariable("client","description",$a_str);
	}

	/**
	* get mysql version
	*/
	function getMySQLVersion()
	{
		return mysql_get_server_info();
	}

	/**
	* check wether current MySQL server is version 4.1.x or higher
	*
	* NOTE: Three sourcecodes use this or a similar handling:
	* - classes/class.ilDBx.php
	* - calendar/classes/class.ilCalInterface.php->setNames
	* - setup/classes/class.ilClient.php
	*/
	function isMysql4_1OrHigher()
	{
		$version = explode(".", $this->getMysqlVersion());
		if ((int)$version[0] >= 5 ||
			((int)$version[0] == 4 && (int)$version[1] >= 1))
		{
			return true;
		}
		
		return false;
	}

	/**
	* connect to client database
	* @return	boolean	true on success
	*/
	function connect()
	{
		// check parameters
		if (!$this->getdbHost() || !$this->getdbName() || !$this->getdbUser())
		{
			$this->error = "empty_fields";
			return false;
		}

		$this->setDSN();

		$this->db = DB::connect($this->dsn,true);

		if (DB::isError($this->db))
		{
			$this->error = $this->db->getMessage()."! not_connected_to_db";
			return false;
		}
		
		// NOTE: Three sourcecodes use this or a similar handling:
		// - classes/class.ilDBx.php
		// - calendar/classes/class.ilCalInterface.php->setNames
		// - setup/classes/class.ilClient.php
		if ($this->isMysql4_1OrHigher())
		{
			$this->db->query("SET NAMES utf8");
			$this->db->query("SET SESSION SQL_MODE = ''");
		}
		
		$this->db_exists = true;
		return true;
	}

	/**
	* check if client db is installed
	* @param	object	db object
	* @return	boolean	true if installed
	*/
	function isInstalledDB(&$a_db)
	{
		$q = "SHOW TABLES";
		$r = $a_db->query($q);
		
		$tables = array();

		while ($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$tables[] = implode($row);
		}

		// check existence of some basic tables from ilias3 to determine if ilias3 is already installed in given database
		if (in_array("object_data",$tables) and in_array("object_reference",$tables) and in_array("usr_data",$tables) and in_array("rbac_ua",$tables))
		{
			$this->db_installed = true;
			return true;
		}
		
		$this->db_installed = false;
		return false;
	}

	/**
	* set the dsn and dsn_host
	*/
	function setDSN()
	{

		$this->dsn_host = "mysql://".$this->getdbUser().":".$this->getdbPass()."@".$this->getdbHost();
		$this->dsn = "mysql://".$this->getdbUser().":".$this->getdbPass()."@".$this->getdbHost()."/".$this->getdbName();
	}

	/**
	* set the host
	* @param	string
	*/
	function setDbHost($a_str)
	{
		$this->ini->setVariable("db","host",$a_str);
	}
	
	/**
	* get db host
	* @return	string	db host
	* 
	*/
	function getDbHost()
	{
		return $this->ini->readVariable("db","host");
	}

	/**
	* set the name of database
	* @param	string
	*/
	function setDbName($a_str)
	{
		$this->ini->setVariable("db","name",$a_str);
	}

	/**
	* get name of database
	* @return	string	name of database
	*/
	function getDbName()
	{
		return $this->ini->readVariable("db","name");
	}

	/**
	* set db user
	* @param	string	db user
	*/
	function setDbUser($a_str)
	{
		$this->ini->setVariable("db","user",$a_str);
	}
	
	/**
	* get db user
	* @return	string	db user
	*/
	function getDbUser()
	{
		return $this->ini->readVariable("db","user");
	}

	/**
	* set db password
	* @param	string
	*/
	function setDbPass($a_str)
	{
		$this->ini->setVariable("db","pass",$a_str);
	}
	
	/**
	* get db password
	* @return	string	db password
	*/
	function getDbPass()
	{
		return $this->ini->readVariable("db","pass");
	}

	/**
	* get client datadir path
	* @return	string	client datadir path
	*/
	function getDataDir()
	{
		return ILIAS_DATA_DIR."/".$this->getId();
	}

	/**
	* get client webspacedir path
	* @return 	string	clietn webspacedir path
	*/
	function getWebspaceDir()
	{
		return ILIAS_ABSOLUTE_PATH."/".ILIAS_WEB_DIR."/".$this->getId();
	}

	/**
	* check database connection
	* @return	boolean
	*/
	function checkDatabaseHost()
	{
		//connect to databasehost
		$db = DB::connect($this->dsn_host);

		if (DB::isError($db))
		{
			$this->error = $db->getMessage()."! Please check database hostname, username & password.";
			return false;
		}
		
		return true;
	}

	/**
	* check database connection with database name
	* @return	boolean
	*/
	function checkDatabaseExists()
	{
		//try to connect to database
		$db = DB::connect($this->dsn);

		if (DB::isError($db))
		{
			return false;
		}
		
		if (!$this->isInstalledDB($db))
		{
			return false;
		}

		return true;
	}

	/**
	* read one value from settings table
	* @access	public
	* @param	string	keyword
	* @return	string	value
	*/
	function getSetting($a_keyword)
	{
		$q = "SELECT value FROM settings WHERE keyword='".$a_keyword."'";
		$r = $this->db->query($q);

		if ($r->numRows() > 0)
		{
			$row = $r->fetchRow();
			return $row[0];
		}
		else
		{
			return false;
		}
	}

	/**
	* read all values from settings table
	* @access	public
	* @return	array	keyword/value pairs
	*/
	function getAllSettings()
	{
		$q = "SELECT * FROM settings";
		$r = $this->db->query($q);

		while ($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$arr[$row["keyword"]] = $row["value"];
		}
		
		return $arr;
	}

	/**
	* write one value to settings table
	* @access	public
	* @param	string		keyword
	* @param	string		value
	* @return	boolean		true on success
	*/
	function setSetting($a_key, $a_val)
	{
		$q = "REPLACE INTO settings SET keyword = '".$a_key."', value = '".$a_val."'";
		$this->db->query($q);
		
		return true;
	}
	
	/**
	* @param	string	url to ilias nic server
	* @return	string	url with required parameters
	*/
	function getURLStringForNIC($a_nic_url)
	{
		$settings = $this->getAllSettings();

		$inst_id = (empty($settings["inst_id"])) ? "0" : $settings["inst_id"];

		// send host information to ilias-nic
		$url = 	$a_nic_url.
				"?cmd=getid".
				"&inst_id=".rawurlencode($inst_id).
				"&hostname=".rawurlencode($_SERVER["SERVER_NAME"]).
				"&ipadr=".rawurlencode($_SERVER["SERVER_ADDR"]).
				"&server_port=".rawurlencode($_SERVER["SERVER_PORT"]).
				"&server_software=".rawurlencode($_SERVER["SERVER_SOFTWARE"]).
				"&inst_name=".rawurlencode($this->ini->readVariable("client","name")).
				"&inst_info=".rawurlencode($this->ini->readVariable("client","description")).
				"&institution=".rawurlencode($settings["inst_institution"]).
				"&http_path=".rawurlencode(ILIAS_HTTP_PATH).
				"&contact_firstname=".rawurlencode($settings["admin_firstname"]).
				"&contact_lastname=".rawurlencode($settings["admin_lastname"]).
				"&contact_title=".rawurlencode($settings["admin_title"]).
				"&contact_position=".rawurlencode($settings["admin_position"]).			
				"&contact_institution=".rawurlencode($settings["admin_institution"]).
				"&contact_street=".rawurlencode($settings["admin_street"]).
				"&contact_pcode=".rawurlencode($settings["admin_zipcode"]).
				"&contact_city=".rawurlencode($settings["admin_city"]).
				"&contact_country=".rawurlencode($settings["admin_country"]).
				"&contact_phone=".rawurlencode($settings["admin_phone"]).
				"&contact_email=".rawurlencode($settings["admin_email"]).
				"&nic_key=".rawurlencode($this->getNICkey()).
				"&version=".rawurlencode($settings["ilias_version"]);
				
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
	function updateNIC($a_nic_url)
	{
		$url = $this->getURLStringForNIC($a_nic_url);

		$conn =fopen($url,"r");
		
		$input = "";
	
		if (!$conn) 
		{
			return false;
		}
		else
		{
			while(!feof($conn))
			{
				$input.= fgets($conn, 4096);
			}

			fclose($conn);
			$line = explode("\n",$input);
			
			$ret = $line;
		}

		$this->nic_status = $ret;

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
	function setNICkey()
	{
		mt_srand((double)microtime()*1000000);
		$nic_key =	md5(str_replace(".","",$_SERVER["SERVER_ADDR"]) +
					mt_rand(100000,999999));
		
		$this->setSetting("nic_key",$nic_key);
		
		$this->nic_key = $nic_key;
		
		return true;
	}
	
	/**
	* get nic_key
	* @access	public
	* @return	string	nic_key
	*/
	function getNICkey()
	{
		$this->nic_key = $this->getSetting("nic_key");
		
		if (empty($this->nic_key))
		{
			$this->setNICkey();
		}
		
		return $this->nic_key;
	}
	
	function getDefaultLanguage()
	{
		return $this->getSetting("language");
	}
	
	function setDefaultLanguage($a_lang_key)
	{
		$this->setSetting("language",$a_lang_key);
		$this->ini->setVariable("language","default",$a_lang_key);
		$this->ini->write();
		
		return true;
	}

	/**
	* get error message and clear error var
	* @return	string	error message
	*/
	function getError()
	{
		$error = $this->error;
		$this->error = "";

		return $error;
	}
	
	/**
	* delete client
	* @param	boolean	remove ini if true
	* @param	boolean	remove db if true
	* @param	boolean remove files if true
	* @return	array	confirmation messages
	* 
	*/
	function delete ($a_ini = true, $a_db = false, $a_files = false)
	{
		if ($a_ini === true and file_exists(ILIAS_ABSOLUTE_PATH."/".ILIAS_WEB_DIR."/".$this->getId()."/client.ini.php"))
		{
			unlink(CLIENT_WEB_DIR."/client.ini.php");
			$msg[] = "ini_deleted";
		}

		if ($a_db === true and $this->db_exists)
		{
			$this->db->query("DROP DATABASE ".$this->getDbName());
			$msg[] = "db_deleted";
		}

		if ($a_files === true and file_exists(CLIENT_WEB_DIR) and is_dir(CLIENT_WEB_DIR))
		{
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
	function create()
	{
		//var_dump($this->getDataDir());exit;
		// create base data dir
		if (!ilUtil::makeDir($this->getDataDir()))
		{
			$this->error = "could_not_create_base_data_dir :".$this->getDataDir();
			return false;
		}

		// create sub dirs in base data dir
		if (!ilUtil::makeDir($this->getDataDir()."/mail"))
		{
			$this->error = "could_not_create_mail_data_dir :".$this->getDataDir()."/mail";
			return false;
		}

		if (!ilUtil::makeDir($this->getDataDir()."/lm_data"))
		{
			$this->error = "could_not_create_lm_data_dir :".$this->getDataDir()."/lm_data";
			return false;
		}

		if (!ilUtil::makeDir($this->getDataDir()."/forum"))
		{
			$this->error = "could_not_create_forum_data_dir :".$this->getDataDir()."/forum";
			return false;
		}

		if (!ilUtil::makeDir($this->getDataDir()."/files"))
		{
			$this->error = "could_not_create_files_data_dir :".$this->getDataDir()."/files";
			return false;
		}

		// create base webspace dir
		if (!ilUtil::makeDir($this->getWebspaceDir()))
		{
			$this->error = "could_not_create_base_webspace_dir :".$this->getWebspaceDir();
			return false;
		}

		// create sub dirs in base webspace dir
		if (!ilUtil::makeDir($this->getWebspaceDir()."/lm_data"))
		{
			$this->error = "could_not_create_lm_webspace_dir :".$this->getWebspaceDir()."/lm_data";
			return false;
		}

		if (!ilUtil::makeDir($this->getWebspaceDir()."/usr_images"))
		{
			$this->error = "could_not_create_usr_images_webspace_dir :".$this->getWebspaceDir()."/usr_images";
			return false;
		}

		if (!ilUtil::makeDir($this->getWebspaceDir()."/mobs"))
		{
			$this->error = "could_not_create_mobs_webspace_dir :".$this->getWebspaceDir()."/mobs";
			return false;
		}

		if (!ilUtil::makeDir($this->getWebspaceDir()."/css"))
		{
			$this->error = "could_not_create_css_webspace_dir :".$this->getWebspaceDir()."/css";
			return false;
		}

		// write client ini
		if (!$this->ini->write())
		{
			$this->error = get_class($this).": ".$this->ini->getError();
			return false;
		}

		return true;
	}
} // END class.ilClient
?>
