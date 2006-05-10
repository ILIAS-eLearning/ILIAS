<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
* ILIAS Initialisation Utility Class
* perform basic setup: init database handler, load configuration file,
* init user authentification & error handler, load object type definitions
*
* @author Alex Killing <alex.killing@databay.de>
* @author Sascha Hofmann <shofmann@databay.de>

* @version $Id$
*/
class ilInitUtil
{
	/**
	* This method provides a global instance of class ilIniFile for the 
	* ilias.ini.php file in variable $ilIliasIniFile.
	*
	* It initializes a lot of constants accordingly to the settings in
	* the ilias.ini.php file.
	*/
	function initIliasIniFile()
	{
		global $ilIliasIniFile;
		
		require_once("classes/class.ilIniFile.php");
		$ilIliasIniFile = new ilIniFile("./ilias.ini.php");
		$GLOBALS['ilIliasIniFile'] =& $ilIliasIniFile;
		$ilIliasIniFile->read();
		
		// initialize constants
		define("ILIAS_DATA_DIR",$ilIliasIniFile->readVariable("clients","datadir"));
		define("ILIAS_WEB_DIR",$ilIliasIniFile->readVariable("clients","path"));
		define("ILIAS_ABSOLUTE_PATH",$ilIliasIniFile->readVariable('server','absolute_path'));

		// logging
		define ("ILIAS_LOG_DIR",$ilIliasIniFile->readVariable("log","path"));
		define ("ILIAS_LOG_FILE",$ilIliasIniFile->readVariable("log","file"));
		define ("ILIAS_LOG_ENABLED",$ilIliasIniFile->readVariable("log","enabled"));
		define ("ILIAS_LOG_LEVEL",$ilIliasIniFile->readVariable("log","level"));
  
		// read path + command for third party tools from ilias.ini
		define ("PATH_TO_CONVERT",$ilIliasIniFile->readVariable("tools","convert"));
		define ("PATH_TO_ZIP",$ilIliasIniFile->readVariable("tools","zip"));
		define ("PATH_TO_UNZIP",$ilIliasIniFile->readVariable("tools","unzip"));
		define ("PATH_TO_JAVA",$ilIliasIniFile->readVariable("tools","java"));
		define ("PATH_TO_HTMLDOC",$ilIliasIniFile->readVariable("tools","htmldoc"));
		define ("PATH_TO_FOP",$ilIliasIniFile->readVariable("tools","fop"));
		
		// read virus scanner settings
		switch ($ilIliasIniFile->readVariable("tools", "vscantype"))
		{
			case "sophos":
				define("IL_VIRUS_SCANNER", "Sophos");
				define("IL_VIRUS_SCAN_COMMAND", $ilIliasIniFile->readVariable("tools", "scancommand"));
				define("IL_VIRUS_CLEAN_COMMAND", $ilIliasIniFile->readVariable("tools", "cleancommand"));
				break;
				
			case "antivir":
				define("IL_VIRUS_SCANNER", "AntiVir");
				define("IL_VIRUS_SCAN_COMMAND", $ilIliasIniFile->readVariable("tools", "scancommand"));
				define("IL_VIRUS_CLEAN_COMMAND", $ilIliasIniFile->readVariable("tools", "cleancommand"));
				break;
				
			default:
				define("IL_VIRUS_SCANNER", "None");
				break;
		}

		$this->__buildHTTPPath();
	}
	
	/**
	* builds http path
	*/
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
		$host = $_SERVER['HTTP_HOST'];

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

	
	/**
	* This method determines the current client and sets the
	* constant CLIENT_ID. 
	*/
	function determineClient()
	{
		global $ilIliasIniFile;

		// check whether ini file object exists
		if (!is_object($ilIliasIniFile))
		{
			die ("Fatal Error: ilInitUtil::determineClient called without initialisation of ILIAS ini file object.");
		}

		// set to default client if empty
		if (!$_COOKIE["ilClientId"])
		{
			// to do: ilias ini raus nehmen
			$client_id = $ilIliasIniFile->readVariable("clients","default");
			setcookie("ilClientId", $client_id);
			$_COOKIE["ilClientId"] = $client_id;
//echo "set cookie";
		}
//echo "-".$_COOKIE["ilClientId"]."-";
		define ("CLIENT_ID", $_COOKIE["ilClientId"]);
	}
	
	/**
	* This method provides a global instance of class ilIniFile for the 
	* client.ini.php file in variable $ilClientIniFile.
	*
	* It initializes a lot of constants accordingly to the settings in
	* the client.ini.php file.
	*
	* Preconditions: ILIAS_WEB_DIR and CLIENT_ID must be set.
	*
	* @return	boolean		true, if no error occured with client init file
	*						otherwise false
	*/
	function initClientIniFile()
	{
		global $ilClientIniFile;

		// check whether ILIAS_WEB_DIR is set.
		if (ILIAS_WEB_DIR == "")
		{
			die ("Fatal Error: ilInitUtil::initClientIniFile called without ILIAS_WEB_DIR.");
		}

		// check whether CLIENT_ID is set.
		if (CLIENT_ID == "")
		{
			die ("Fatal Error: ilInitUtil::initClientIniFile called without CLIENT_ID.");
		}

		$ini_file = "./".ILIAS_WEB_DIR."/".CLIENT_ID."/client.ini.php";

		// get settings from ini file
		require_once("classes/class.ilIniFile.php");
		$ilClientIniFile = new ilIniFile($ini_file);
		$GLOBALS['ilClientIniFile'] =& $ilClientIniFile; 
		$ilClientIniFile->read();

		// if no ini-file found switch to setup routine
		if ($ilClientIniFile->ERROR != "")
		{
			return false;
		}

		// set constants
		define ("DEBUG",$ilClientIniFile->readVariable("system","DEBUG"));
		define ("DEVMODE",$ilClientIniFile->readVariable("system","DEVMODE"));
		define ("ROOT_FOLDER_ID",$ilClientIniFile->readVariable('system','ROOT_FOLDER_ID'));
		define ("SYSTEM_FOLDER_ID",$ilClientIniFile->readVariable('system','SYSTEM_FOLDER_ID'));
		define ("ROLE_FOLDER_ID",$ilClientIniFile->readVariable('system','ROLE_FOLDER_ID'));
		define ("MAIL_SETTINGS_ID",$ilClientIniFile->readVariable('system','MAIL_SETTINGS_ID'));

		define ("SYSTEM_MAIL_ADDRESS",$ilClientIniFile->readVariable('system','MAIL_SENT_ADDRESS')); // Change SS
		define ("MAIL_REPLY_WARNING",$ilClientIniFile->readVariable('system','MAIL_REPLY_WARNING')); // Change SS

		define ("MAXLENGTH_OBJ_TITLE",$ilClientIniFile->readVariable('system','MAXLENGTH_OBJ_TITLE'));
		define ("MAXLENGTH_OBJ_DESC",$ilClientIniFile->readVariable('system','MAXLENGTH_OBJ_DESC'));

		define ("CLIENT_DATA_DIR",ILIAS_DATA_DIR."/".CLIENT_ID);
		define ("CLIENT_WEB_DIR",ILIAS_ABSOLUTE_PATH."/".ILIAS_WEB_DIR."/".CLIENT_ID);
		define ("CLIENT_NAME",$ilClientIniFile->readVariable('client','name')); // Change SS

		// build dsn of database connection and connect
		define ("IL_DSN", $ilClientIniFile->readVariable("db","type").
					 "://".$ilClientIniFile->readVariable("db", "user").
					 ":".$ilClientIniFile->readVariable("db", "pass").
					 "@".$ilClientIniFile->readVariable("db", "host").
					 "/".$ilClientIniFile->readVariable("db", "name"));

		return true;
	}
	
	/**
	* initialise database object $ilDB
	*
	* precondition: IL_DSN must be set
	*/
	function initDatabase()
	{
		global $ilDB;

		// check whether ILIAS_WEB_DIR is set.
		if (IL_DSN == "")
		{
			die ("Fatal Error: ilInitUtil::initDatabase called without IL_DSN.");
		}

		// build dsn of database connection and connect
		require_once("classes/class.ilDBx.php");
		$ilDB = new ilDBx(IL_DSN);
		$GLOBALS['ilDB'] =& $ilDB;
	}
	
	
	/**
	* initialise $ilSettings object and define constants
	*/
	function initSettings()
	{
		global $ilSetting;
		
		require_once("Services/Administration/classes/class.ilSetting.php");
		$ilSetting = new ilSetting();
		$GLOBALS['ilSetting'] =& $ilSetting;

		// set anonymous user & role id and system role id
		define ("ANONYMOUS_USER_ID", $ilSetting->get("anonymous_user_id"));
		define ("ANONYMOUS_ROLE_ID", $ilSetting->get("anonymous_role_id"));
		define ("SYSTEM_USER_ID", $ilSetting->get("system_user_id"));
		define ("SYSTEM_ROLE_ID", $ilSetting->get("system_role_id"));
		
		// recovery folder
		define ("RECOVERY_FOLDER_ID", $ilSetting->get("recovery_folder_id"));

		// installation id
		define ("IL_INST_ID", $ilSetting->get("inst_id"));

	}
}
?>
