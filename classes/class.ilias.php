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
* ILIAS base class
* perform basic setup: init database handler, load configuration file,
* init user authentification & error handler, load object type definitions
*
* @author Sascha Hofmann <shofmann@databay.de>
* @version $Id$
*
* @package ilias-core
* @todo review the concept how the object type definition is loaded. We need a concept to
* edit the definitions via webfrontend in the admin console.
*/
class ILIAS
{
	/**
	* ini file
	* @var string
	*/
 	var $INI_FILE;

	/**
	* database connector
	* @var string
	* @access public
	*/
	var $dsn = "";

	/**
	* database handle
	* @var object database
	* @access private
	*/
	var $db;

	/**
	* template path
	* @var string
	* @access private
	*/
	var $tplPath = "./templates/";

	/**
	* user account
	* @var object user
	* @access public
	*/
	var $account;

	/**
	* auth parameters
	* @var array
	* @access private
	*/
	var $auth_params = array();

	/**
	* auth handler
	* @var object auth
	* @access public
	*/
	var $auth;

 	/**
	* system settings
	* @var array
	* @access public
	*/
	var $ini = array();

	/**
	* Error Handling
	* @var object Error
	* @access public
	*/
	var $error_obj;

	/**
	* object factory
	*
	* @var object factory
	* @access public
	*/
	var $obj_factory;

	/**
	* styles
	*
	* @var	array	list of stylesheets
	* @access	public
	*/
	var $styles;

	/**
	* skins (template sets)
	*
	* @var	array	list of skins
	* @access	public
	*/
	var $skins;
	
	/**
	* Constructor
	* setup ILIAS global object
	* @access	public
	*/
	function ILIAS($a_client_id = 0)
	{
		global $ilErr, $ilDB;

		// load setup.ini
		$this->ini_ilias = new ilIniFile("./ilias.ini.php");
		$this->ini_ilias->read();

		define("ILIAS_DATA_DIR",$this->ini_ilias->readVariable("clients","datadir"));
		define("ILIAS_WEB_DIR",$this->ini_ilias->readVariable("clients","path"));

		$this->__buildHTTPPath();
		define ("ILIAS_ABSOLUTE_PATH",$this->ini_ilias->readVariable('server','absolute_path'));

		// logging
		define ("ILIAS_LOG_DIR",$this->ini_ilias->readVariable("log","path"));
		define ("ILIAS_LOG_FILE",$this->ini_ilias->readVariable("log","file"));
		define ("ILIAS_LOG_ENABLED",$this->ini_ilias->readVariable("log","enabled"));
		define ("ILIAS_LOG_LEVEL",$this->ini_ilias->readVariable("log","level"));
  
		// read path + command for third party tools from ilias.ini
		define ("PATH_TO_CONVERT",$this->ini_ilias->readVariable("tools","convert"));
		define ("PATH_TO_ZIP",$this->ini_ilias->readVariable("tools","zip"));
		define ("PATH_TO_UNZIP",$this->ini_ilias->readVariable("tools","unzip"));
		define ("PATH_TO_JAVA",$this->ini_ilias->readVariable("tools","java"));
		define ("PATH_TO_HTMLDOC",$this->ini_ilias->readVariable("tools","htmldoc"));
		define ("PATH_TO_FOP",$this->ini_ilias->readVariable("tools","fop"));
		
		// read virus scanner settings
		switch ($this->ini_ilias->readVariable("tools", "vscantype"))
		{
			case "sophos":
				define("IL_VIRUS_SCANNER", "Sophos");
				define("IL_VIRUS_SCAN_COMMAND", $this->ini_ilias->readVariable("tools", "scancommand"));
				define("IL_VIRUS_CLEAN_COMMAND", $this->ini_ilias->readVariable("tools", "cleancommand"));
				break;
				
			case "antivir":
				define("IL_VIRUS_SCANNER", "AntiVir");
				define("IL_VIRUS_SCAN_COMMAND", $this->ini_ilias->readVariable("tools", "scancommand"));
				define("IL_VIRUS_CLEAN_COMMAND", $this->ini_ilias->readVariable("tools", "cleancommand"));
				break;
				
			default:
				define("IL_VIRUS_SCANNER", "None");
				break;
		}

		// set to default client if empty
		if (!$a_client_id)
		{
			$this->client_id = $this->ini_ilias->readVariable("clients","default");
			setcookie("ilClientId",$this->client_id);
			$_COOKIE["ilClientId"] = $this->client_id;
		}
		else
		{
			$this->client_id = $_COOKIE["ilClientId"];
		}

		$this->INI_FILE = "./".ILIAS_WEB_DIR."/".$this->client_id."/client.ini.php";

//		$this->PEAR();

		// prepare file access to work with safe mode
		umask(0117);

		// get settings from ini file
		$this->ini = new ilIniFile($this->INI_FILE);
		$this->ini->read();

		// if no ini-file found switch to setup routine
		if ($this->ini->ERROR != "")
		{
			ilUtil::redirect("./setup/setup.php");
		}

		if (!$this->ini->readVariable("client","access"))
		{
			die("client disabled");
		}

		// set constants
		define ("DEBUG",$this->ini->readVariable("system","DEBUG"));
		define ("DEVMODE",$this->ini->readVariable("system","DEVMODE"));
		define ("ROOT_FOLDER_ID",$this->ini->readVariable('system','ROOT_FOLDER_ID'));
		define ("SYSTEM_FOLDER_ID",$this->ini->readVariable('system','SYSTEM_FOLDER_ID'));
		define ("ROLE_FOLDER_ID",$this->ini->readVariable('system','ROLE_FOLDER_ID'));
		define ("MAIL_SETTINGS_ID",$this->ini->readVariable('system','MAIL_SETTINGS_ID'));

		define ("SYSTEM_MAIL_ADDRESS",$this->ini->readVariable('system','MAIL_SENT_ADDRESS')); // Change SS
		define ("MAIL_REPLY_WARNING",$this->ini->readVariable('system','MAIL_REPLY_WARNING')); // Change SS

		define ("MAXLENGTH_OBJ_TITLE",$this->ini->readVariable('system','MAXLENGTH_OBJ_TITLE'));
		define ("MAXLENGTH_OBJ_DESC",$this->ini->readVariable('system','MAXLENGTH_OBJ_DESC'));

		define ("CLIENT_DATA_DIR",ILIAS_DATA_DIR."/".$this->client_id);
		define ("CLIENT_WEB_DIR",ILIAS_ABSOLUTE_PATH."/".ILIAS_WEB_DIR."/".$this->client_id);
		define ("CLIENT_ID",$this->client_id);
		define ("CLIENT_NAME",$this->ini->readVariable('client','name')); // Change SS

		// build dsn of database connection and connect
		$this->dsn = $this->ini->readVariable("db","type").
					 "://".$this->ini->readVariable("db", "user").
					 ":".$this->ini->readVariable("db", "pass").
					 "@".$this->ini->readVariable("db", "host").
					 "/".$this->ini->readVariable("db", "name");

		$this->db = new ilDBx($this->dsn);

		// Error Handling
		$this->error_obj =& $ilErr;

		// moved here from inc.header.php (db_set_save_handler needs db object)
		$ilDB = $this->db;
		$GLOBALS['ilDB'] =& $ilDB;

		// set session.save_handler to "user" & set expiry time
		if(ini_get('session.save_handler') != 'user')
		{
			ini_set("session.save_handler", "user");
		}
		
		// moved here from inc.header.php
		if (!db_set_save_handler())
		{
			$message = "Please turn off Safe mode OR set session.save_handler to \"user\" in your php.ini";
			$this->raiseError($message, $this->error_obj->FATAL);
		}

		// set anonymous user & role id and system role id
		define ("ANONYMOUS_USER_ID",$this->getSetting("anonymous_user_id"));
		define ("ANONYMOUS_ROLE_ID",$this->getSetting("anonymous_role_id"));
		define ("SYSTEM_USER_ID",$this->getSetting("system_user_id"));
		define ("SYSTEM_ROLE_ID",$this->getSetting("system_role_id"));
		define ("RECOVERY_FOLDER_ID",$this->getSetting("recovery_folder_id"));

		// installation id
		define ("IL_INST_ID", $this->getSetting("inst_id"));

		// define auth modes
		define ("AUTH_LOCAL",1);
		define ("AUTH_LDAP",2);
		define ("AUTH_RADIUS",3);
		define ("AUTH_SCRIPT",4);
		define ("AUTH_SHIBBOLETH",5);
		
		// get default auth mode 
		//$default_auth_mode = $this->getSetting("auth_mode");
		define ("AUTH_DEFAULT", $this->getSetting("auth_mode") ? $this->getSetting("auth_mode") : AUTH_LOCAL);
		
		// set local auth mode (1) in case database wasn't updated
		/*if ($default_auth_mode === false)
		{
			$default_auth_mode = AUTH_LOCAL;
		}*/
		
		// determine authentication method if no session is found and username & password is posted
        if (empty($_SESSION) ||
            (!isset($_SESSION['_authsession']['registered']) ||
             $_SESSION['_authsession']['registered'] !== true))
        {
			// no sesssion found
			if ($_POST['username'] != '' and $_POST['password'] != '')
			{
				include_once(ILIAS_ABSOLUTE_PATH.'/classes/class.ilAuthUtils.php');
				$user_auth_mode = ilAuthUtils::_getAuthModeOfUser($_POST['username'],$_POST['password'],$this->db);
			}
        }
		
		// If Shibboleth is active and the user is authenticated
		// we set auth_mode to Shibboleth
		if (	
				$this->getSetting("shib_active")
				&& $_SERVER[$this->getSetting("shib_login")]
			)
		{
			define ("AUTH_CURRENT",AUTH_SHIBBOLETH);
		}
		else
		{
			define ("AUTH_CURRENT",$user_auth_mode);
		}
		
		switch (AUTH_CURRENT)
		{
			case AUTH_LOCAL:
				// build option string for PEAR::Auth
				$this->auth_params = array(
											'dsn'		  => $this->dsn,
											'table'       => $this->ini->readVariable("auth", "table"),
											'usernamecol' => $this->ini->readVariable("auth", "usercol"),
											'passwordcol' => $this->ini->readVariable("auth", "passcol")
											);
				// We use MySQL as storage container
				$this->auth = new Auth("DB", $this->auth_params,"",false);
				break;
			
			case AUTH_LDAP:
				$settings = $this->getAllSettings();

				// build option string for PEAR::Auth
				$this->auth_params = array(
											'host'		=> $settings["ldap_server"],
											'port'		=> $settings["ldap_port"],
											'basedn'	=> $settings["ldap_basedn"],
											'userdn'	=> $settings["ldap_search_base"],
											'useroc'	=> $settings["ldap_objectclass"],
											'userattr'	=> $settings["ldap_login_key"]
											);
				$this->auth = new Auth("LDAP", $this->auth_params,"",false);
				break;
				
			case AUTH_RADIUS:
				include_once('classes/class.ilRADIUSAuthentication.php');
				$radius_servers = ilRADIUSAuthentication::_getServers($this->db);

				$settings = $this->getAllSettings();
				
				foreach ($radius_servers as $radius_server)
				{
					$rad_params['servers'][] = array($radius_server,$settings["radius_port"],$settings["radius_shared_secret"]);
				}
				
				// build option string for PEAR::Auth
				//$this->auth_params = array($rad_params);
				$this->auth_params = $rad_params;
				$this->auth = new Auth("RADIUS", $this->auth_params,"",false);
				break;
				
			case AUTH_SHIBBOLETH:
				$settings = $this->getAllSettings();

				// build option string for SHIB::Auth
				$this->auth_params = array();
				$this->auth = new ShibAuth($this->auth_params,true);
				break;
				
			default:
				// build option string for PEAR::Auth
				$this->auth_params = array(
											'dsn'		  => $this->dsn,
											'table'       => $this->ini->readVariable("auth", "table"),
											'usernamecol' => $this->ini->readVariable("auth", "usercol"),
											'passwordcol' => $this->ini->readVariable("auth", "passcol")
											);
				// We use MySQL as storage container
				$this->auth = new Auth("DB", $this->auth_params,"",false);
				break;

		}

		$this->auth->setIdle($this->ini->readVariable("session","expire"), false);
		$this->auth->setExpire(0);
		ini_set("session.cookie_lifetime", "0");

		// create instance of object factory
		require_once("classes/class.ilObjectFactory.php");
		$this->obj_factory =& new ilObjectFactory();
	}

	/**
	* Destructor
	* @access	private
	* @return	boolean
	*/
	function _ILIAS()
	{
		if ($this->ini->readVariable("db", "type") != "")
		{
			$this->db->disconnect();
		}
		
		return true;
	}
	
	
	/**
	* set authentication error (should be set after Auth->start() via
	* $ilias->setAuthError($ilErr->getLastError());
	*
	* @param	object		$a_error_obj	pear error object
	*/
	function setAuthError($a_error_obj)
	{
		$this->auth_error =& $a_error_obj;
	}
	
	/**
	* get (last) authentication error object
	*/
	function &getAuthError()
	{
		return $this->auth_error;
	}

	/**
	* read one value from settingstable
	* @access	public
	* @param	string	keyword
	* @param	string	default_value This value is returned, when no setting has
    *								  been found for the keyword.
	* @return	string	value
	*/
	function getSetting($a_keyword, $a_default_value = false)
	{

		if ($a_keyword == "ilias_version")
		{
			return ILIAS_VERSION;
		}
		
		$query = "SELECT value FROM settings WHERE keyword='".$a_keyword."'";
		$res = $this->db->query($query);

		if ($res->numRows() > 0)
		{
			$row = $res->fetchRow();
			return $row[0];
			//return ilUtil::stripSlashes($row[0]);
		}
		else
		{
			return $a_default_value;
		}
	}
	
	/**
	* delete one value from settingstable
	* @access	public
	* @param	string	keyword
	* @return	string	value
	*/
	function deleteSetting($a_keyword)
	{
		$query = "DELETE FROM settings WHERE keyword = '".$a_keyword."'";
		$this->db->query($query);

		return true;
	}


	/**
	* read all values from settingstable
	* @access	public
	* @return	array	keyword/value pairs
	*/
	function getAllSettings()
	{
		$query = "SELECT * FROM settings";
		$res = $this->db->query($query);

		while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$arr[$row["keyword"]] = $row["value"];
			//$arr[$row["keyword"]] = ilUtil::stripSlashes($row["value"]);
		}

		return $arr;
	}

	/**
	* write one value to db-table settings
	* @access	public
	* @param	string		keyword
	* @param	string		value
	* @return	boolean		true on success
	* 
	* TODO: change to replace-statement
	*/
	function setSetting($a_key, $a_val)
	{
		$sql = "DELETE FROM settings WHERE keyword='".$a_key."'";
		$r = $this->db->query($sql);

		$sql = "INSERT INTO settings (keyword, value) VALUES ('".$a_key."','".addslashes($a_val)."')";
		$r = $this->db->query($sql);

		return true;
	}

	/**
	*
	* /// deprecated: Use $templates = $styleDefinition->getAllTemplates() instead
	*
	* skin system: get all available skins from template directory
	* and store them in $this->skins
	* @access	public
	* @return	boolean	false if no skin was found
	* @author	Peter Gabriel <pgabriel@databay.de>
	*/
	/*
	function getSkins()
	{
		$skins = array();

		//open directory for reading and search for subdirectories
		//$tplpath = $this->ini->readVariable("server", "tpl_path");
		//$tplpath = "./templates";

		if ($dp = @opendir($this->tplPath))
		{
			while (($file = readdir($dp)) != false)
			{
				//is the file a directory?
				if (is_dir($this->tplPath.$file) && $file != "." && $file != ".." && $file != "CVS")
				{
					$skins[] = array(
						"name" => $file
					);
				}
			} // while
		}
		else
		{
			return false;
		}

		$this->skins = $skins;

		return true;
	}*/

	/**
	*
	* /// deprecated: use 	ilStyleDefinition()->getStyles() instead
	*
	*
	* skin system: get all available styles from current templates
	* and store them in $this->styles
	* @access	public
	* @param	string	name of template set/directory name
	* @return	boolean	false if no style was found
	* @author	Peter Gabriel <pgabriel@databay.de>
	*/
	/*
	function getStyles($a_skin)
	{
		$styles = array();

		//open directory for reading and search for subdirectories
		//$tplpath = $this->ini->readVariable("server", "tpl_path")."/".$skin;
		//$tplpath = "./templates/".$a_skin;

		if ($dp = @opendir($this->tplPath.$a_skin))
		{
			while (($file = readdir($dp)) != false)
			{
				//is the file a stylesheet?
				if (strpos($file, ".css") > 0)
				{
					$styles[] = array(
										"name" => substr($file,0,-4)
									);
				}
			} // while
		}
		else
		{
			return false;
		}

		$this->styles = $styles;

		return true;
	}*/

	/**
	* get first available stylesheet from skindirectory
	* @param	string
	* @return	string	style name
	* @access	public
	*/
	function getFirstStyle($a_skin)
	{
		if (!is_array($this->styles))
		{
			$this->getStyles($a_skin);
		}

		return $this->styles[0]["name"];
	}
	
	/**
	* check if a template name exists on the server
	* @param	string	template name
	* @return	boolean	true if file exists
	* @access	public
	*/
	function checkTemplate($a_name)
	{
		return file_exists($this->tplPath.$a_name);
	}

	/**
	* get current user account
	*/
	function &getCurrentUser()
	{
		return $this->account;
	}
	
	function getClientId()
	{
		return $this->client_id;
	}
	
	/**
	* wrapper for downward compability
	*/
	function raiseError($a_msg,$a_err_obj)
	{
		global $ilErr;

		$ilErr->raiseError($a_msg,$a_err_obj);
	}


	function __buildHTTPPath()
	{
		if($_SERVER['HTTPS'] == 'on')
		{
			$protocol = 'https://';
		}
		else
		{
			$protocol = 'http://';
		}
		$host = $_SERVER['SERVER_NAME'];

		if(!defined('ILIAS_MODULE'))
		{
			$path = pathinfo($_SERVER['REQUEST_URI']);
			if(!$path['extension'])
			{
				$uri = $_SERVER['REQUEST_URI'];
			}
			else
			{
				$uri = dirname($_SERVER['REQUEST_URI']);
			}
		}
		else
		{
			// if in module remove module name from HTTP_PATH
			$path = dirname($_SERVER['REQUEST_URI']);
			
			// dirname cuts the last directory from a directory path e.g content/classes return content
			
			$module = ilUtil::removeTrailingPathSeparators(ILIAS_MODULE);

			$dirs = explode('/',$module);
			$uri = $path;
			foreach($dirs as $dir)
			{
				$uri = dirname($uri);
			}
		}
		return define('ILIAS_HTTP_PATH',$protocol.$host.$uri);
	}

} // END class.ilias
?>
