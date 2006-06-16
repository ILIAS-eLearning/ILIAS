<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
* static utility functions used to manage authentication modes
*
* @author Sascha Hofmann <saschahofmann@gmx.de>
* @version $Id$
* @package ilias
*
*/

class ilAuthUtils
{
	
	/**
	* initialises $ilAuth 
	*/
	function _initAuth()
	{
		global $ilAuth, $ilSetting, $ilDB, $ilClientIniFile;
		
		// check whether settings object is available
		if (!is_object($ilSetting))
		{
			die ("Fatal Error: ilAuthUtils::_initAuth called without ilSetting.");
		}

		// check whether database object is available
		if (!is_object($ilDB))
		{
			die ("Fatal Error: ilAuthUtils::_initAuth called without ilDB.");
		}

		// check whether client ini file object is available
		if (!is_object($ilClientIniFile))
		{
			die ("Fatal Error: ilAuthUtils::_initAuth called without ilClientIniFile.");
		}

		// define auth modes
		define ("AUTH_LOCAL",1);
		define ("AUTH_LDAP",2);
		define ("AUTH_RADIUS",3);
		define ("AUTH_SCRIPT",4);
		define ("AUTH_SHIBBOLETH",5);
		define ("AUTH_CAS",6);

		// get default auth mode 
		//$default_auth_mode = $this->getSetting("auth_mode");
		define ("AUTH_DEFAULT", $ilSetting->get("auth_mode") ? $ilSetting->get("auth_mode") : AUTH_LOCAL);
		
		// set local auth mode (1) in case database wasn't updated
		/*if ($default_auth_mode === false)
		{
			$default_auth_mode = AUTH_LOCAL;
		}*/
		
		// determine authentication method if no session is found and username & password is posted
		// does this if statement make any sense? we enter this block nearly everytime.
        if (empty($_SESSION) ||
            (!isset($_SESSION['_authsession']['registered']) ||
             $_SESSION['_authsession']['registered'] !== true))
        {
			// no sesssion found
			if ($_POST['username'] != '' and $_POST['password'] != '')
			{
				//include_once(ILIAS_ABSOLUTE_PATH.'/classes/class.ilAuthUtils.php');
				$user_auth_mode = ilAuthUtils::_getAuthModeOfUser($_POST['username'], $_POST['password'], $ilDB);
			}
        }
		
		// If Shibboleth is active and the user is authenticated
		// we set auth_mode to Shibboleth
		if (	
				$ilSetting->get("shib_active")
				&& $_SERVER[$ilSetting->get("shib_login")]
			)
		{
			define ("AUTH_CURRENT", AUTH_SHIBBOLETH);
		}
		// check CAS authentication
		else if ($ilSetting->get("cas_active"))
		{
			include_once("Services/CAS/classes/class.ilCASAuth.php");
			$auth_params = array(
				"server_version" => CAS_VERSION_2_0,
				"server_hostname" => $ilSetting->get("cas_server"),
				"server_port" => $ilSetting->get("cas_port"),
				"server_uri" => $ilSetting->get("cas_uri"));

			$ilCASAuth = new ilCASAuth($auth_params);
			
			if ($_GET["forceCASLogin"] == "1")
			{
				$ilCASAuth->forceCASAuth();
			}

			if ($ilCASAuth->checkCASAuth())
			{
				define ("AUTH_CURRENT", AUTH_CAS);
			}
			else
			{
				define ("AUTH_CURRENT", $user_auth_mode);
			}
		}
		else
		{
			define ("AUTH_CURRENT", $user_auth_mode);
		}

		switch (AUTH_CURRENT)
		{
			case AUTH_LOCAL:
				// build option string for PEAR::Auth
				$auth_params = array(
											'dsn'		  => IL_DSN,
											'table'       => $ilClientIniFile->readVariable("auth", "table"),
											'usernamecol' => $ilClientIniFile->readVariable("auth", "usercol"),
											'passwordcol' => $ilClientIniFile->readVariable("auth", "passcol")
											);
				// We use MySQL as storage container
				$ilAuth = new Auth("DB", $auth_params,"",false);
				break;
			
			case AUTH_LDAP:
				$settings = $ilSetting->getAll();

				// build option string for PEAR::Auth
				$auth_params = array(
											'host'		=> $settings["ldap_server"],
											'port'		=> $settings["ldap_port"],
											'basedn'	=> $settings["ldap_basedn"],
											'userdn'	=> $settings["ldap_search_base"],
											'useroc'	=> $settings["ldap_objectclass"],
											'userattr'	=> $settings["ldap_login_key"]
											);
				$ilAuth = new Auth("LDAP", $auth_params,"",false);
				break;
				
			case AUTH_RADIUS:
				include_once('classes/class.ilRADIUSAuthentication.php');
				$radius_servers = ilRADIUSAuthentication::_getServers($ilDB);

				$settings = $ilSetting->getAll();
				
				foreach ($radius_servers as $radius_server)
				{
					$rad_params['servers'][] = array($radius_server,$settings["radius_port"],$settings["radius_shared_secret"]);
				}
				
				// build option string for PEAR::Auth
				//$this->auth_params = array($rad_params);
				$auth_params = $rad_params;
				$ilAuth = new Auth("RADIUS", $auth_params,"",false);
				break;
				
			case AUTH_SHIBBOLETH:
			
				// build option string for SHIB::Auth
				$auth_params = array();
				$ilAuth = new ShibAuth($auth_params,true);
				break;
				
			case AUTH_CAS:
				$ilAuth =& $ilCASAuth;
				$ilAuth->forceCASAuth();
				break;
				
			default:
				// build option string for PEAR::Auth
				$auth_params = array(
											'dsn'		  => IL_DSN,
											'table'       => $ilClientIniFile->readVariable("auth", "table"),
											'usernamecol' => $ilClientIniFile->readVariable("auth", "usercol"),
											'passwordcol' => $ilClientIniFile->readVariable("auth", "passcol")
											);
				// We use MySQL as storage container
				$ilAuth = new Auth("DB", $auth_params,"",false);
				break;

		}

		$ilAuth->setIdle($ilClientIniFile->readVariable("session","expire"), false);
		$ilAuth->setExpire(0);
		ini_set("session.cookie_lifetime", "0");

		$GLOBALS['ilAuth'] =& $ilAuth;
	}
	
	function _getAuthModeOfUser($a_username,$a_password,$a_db_handler = '')
	{
		global $ilDB;

		$db =& $ilDB;
		
		if ($a_db_handler != '')
		{
			$db =& $a_db_handler;
		}
		
		$q = "SELECT auth_mode FROM usr_data WHERE ".
			 "login='".$a_username."' AND ".
			 "passwd='".md5($a_password)."'";
		$r = $db->query($q);
		
		$row = $r->fetchRow(DB_FETCHMODE_OBJECT);

		return ilAuthUtils::_getAuthMode($row->auth_mode,$db);
	}
	
	function _getAuthMode($a_auth_mode,$a_db_handler = '')
	{
		global $ilDB;
		
		$db =& $ilDB;
		
		if ($a_db_handler != '')
		{
			$db =& $a_db_handler;
		}

		switch ($a_auth_mode)
		{
			case "local":
				return AUTH_LOCAL;
				break;
				
			case "ldap":
				return AUTH_LDAP;
				break;
				
			case "radius":
				return AUTH_RADIUS;
				break;
				
			case "script":
				return AUTH_SCRIPT;
				break;
				
			case "shibboleth":
				return AUTH_SHIBBOLETH;
				break;

			case "cas":
				return AUTH_CAS;
				break;

			default:
				$q = "SELECT value FROM settings WHERE ".
			 		 "keyword='auth_mode'";
				$r = $db->query($q);
				$row = $r->fetchRow();
				return $row[0];
				break;	
		}
	}
	
	function _getAuthModeName($a_auth_key)
	{
		global $ilias;

		switch ($a_auth_key)
		{
			case AUTH_LOCAL:
				return "local";
				break;
				
			case AUTH_LDAP:
				return "ldap";
				break;
				
			case AUTH_RADIUS:
				return "radius";
				break;
				
			case AUTH_SCRIPT:
				return "script";
				break;
				
			case AUTH_SHIBBOLETH:
				return "shibboleth";
				break;
				
			default:
				return "default";
				break;	
		}
	}
	
	function _getActiveAuthModes()
	{
		global $ilias;
		
		$modes = array(
						'default'	=> $ilias->getSetting("auth_mode"),
						'local'		=> AUTH_LOCAL
						);
		
		if ($ilias->getSetting("ldap_active")) $modes['ldap'] = AUTH_LDAP;
		if ($ilias->getSetting("radius_active")) $modes['radius'] = AUTH_RADIUS;
		if ($ilias->getSetting("shibboleth_active")) $modes['shibboleth'] = AUTH_SHIBBOLETH;
		if ($ilias->getSetting("script_active")) $modes['script'] = AUTH_SCRIPT;

		return $modes;
	}
	
}
?>
