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

// define auth modes
define ("AUTH_LOCAL",1);
define ("AUTH_LDAP",2);
define ("AUTH_RADIUS",3);
define ("AUTH_SCRIPT",4);
define ("AUTH_SHIBBOLETH",5);
define ("AUTH_CAS",6);
define ("AUTH_SOAP",7);

define('AUTH_SOAP_NO_ILIAS_USER', -100);
define('AUTH_LDAP_NO_ILIAS_USER',-200);
define('AUTH_RADIUS_NI_ILIAS_USER',-300);


// an external user cannot be found in ilias, but his email address
// matches one or more ILIAS users
define('AUTH_SOAP_NO_ILIAS_USER_BUT_EMAIL', -101);
define('AUTH_CAS_NO_ILIAS_USER', -90);

/**
* static utility functions used to manage authentication modes
*
* @author Sascha Hofmann <saschahofmann@gmx.de>
* @version $Id$
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
//var_dump($_SESSION);
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

		// get default auth mode 
		//$default_auth_mode = $this->getSetting("auth_mode");
		define ("AUTH_DEFAULT", $ilSetting->get("auth_mode") ? $ilSetting->get("auth_mode") : AUTH_LOCAL);
		
		// set local auth mode (1) in case database wasn't updated
		/*if ($default_auth_mode === false)
		{
			$default_auth_mode = AUTH_LOCAL;
		}*/
//var_dump($_SESSION);
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
				
				if ($user_auth_mode == AUTH_CAS && $ilSetting->get("cas_allow_local"))
				{
					$user_auth_mode = AUTH_LOCAL;
				}
				if ($user_auth_mode == AUTH_SOAP && $ilSetting->get("soap_auth_allow_local"))
				{
					$user_auth_mode = AUTH_LOCAL;
				}
			}
        }
		
		// to do: other solution?
		if (!$ilSetting->get("soap_auth_active") && $user_auth_mode == AUTH_SOAP)
		{
			$user_auth_mode = AUTH_LOCAL;
		}
		
//var_dump($_SESSION);
//echo "1-".$ilSetting->get("soap_auth_active")."-";
		// if soap authentication activated and soap credentials given
		if (($ilSetting->get("soap_auth_active") && !empty($_GET["ext_uid"])
			&& !empty($_GET["soap_pw"])) || $user_auth_mode == AUTH_SOAP)
		{
			include_once("Services/SOAPAuth/classes/class.ilSOAPAuth.php");
			
			if (!is_object($GLOBALS['ilSOAPAuth']))
			{
				$auth_params = array(
					"server_hostname" => $ilSetting->get("soap_auth_server"),
					"server_port" => $ilSetting->get("soap_auth_port"),
					"server_uri" => $ilSetting->get("soap_auth_uri"),
					"https" => $ilSetting->get("soap_auth_use_https"),
					"namespace" => $ilSetting->get("soap_auth_namespace"),
					"use_dotnet" => $ilSetting->get("soap_auth_use_dotnet")
					);
				// this starts already the session, AccountId is '' _authsession is null
				// (assuming that ilSOAPAuth constructor calls Auth constructor
				$ilSOAPAuth = new ilSOAPAuth($auth_params);
				$GLOBALS['ilSOAPAuth'] =& $ilSOAPAuth;
			}
			else
			{
				$ilSOAPAuth =& $GLOBALS['ilSOAPAuth'];
			}

			define ("AUTH_CURRENT", AUTH_SOAP);
		}
		// if Shibboleth is active and the user is authenticated
		// we set auth_mode to Shibboleth
		else if (	$ilSetting->get("shib_active")
				&& $_SERVER[$ilSetting->get("shib_login")])
		{
			define ("AUTH_CURRENT", AUTH_SHIBBOLETH);
		}
		// check CAS authentication
		else if ($ilSetting->get("cas_active"))
		{
			include_once("Services/CAS/classes/class.ilCASAuth.php");
			
			if (!is_object($GLOBALS['ilCASAuth']))
			{
				$auth_params = array(
					"server_version" => CAS_VERSION_2_0,
					"server_hostname" => $ilSetting->get("cas_server"),
					"server_port" => $ilSetting->get("cas_port"),
					"server_uri" => $ilSetting->get("cas_uri"));
//echo "II";
//var_dump($_SESSION);
				$ilCASAuth = new ilCASAuth($auth_params);
//var_dump($_SESSION);
				$GLOBALS['ilCASAuth'] =& $ilCASAuth;
			}
			else
			{
				$ilCASAuth =& $GLOBALS['ilCASAuth'];
			}
			
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
				//session_unset();
			}
		}
		else
		{
			define ("AUTH_CURRENT", $user_auth_mode);
		}
//var_dump($_SESSION);
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
				// this starts already the session, AccountId is '' _authsession is null
				$ilAuth = new Auth("DB", $auth_params,"",false);
				break;
			
			case AUTH_LDAP:

				include_once 'Services/LDAP/classes/class.ilAuthLDAP.php';
				$ilAuth = new ilAuthLDAP();
				/*
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
				*/
				break;
				
			case AUTH_RADIUS:
				include_once('Services/Radius/classes/class.ilAuthRadius.php');
				$ilAuth = new ilAuthRadius();
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
				
			case AUTH_SOAP:
				$ilAuth =& $ilSOAPAuth;
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
//var_dump($_SESSION);
				$ilAuth = new Auth("DB", $auth_params,"",false);
//var_dump($_SESSION);
				break;

		}

		$ilAuth->setIdle($ilClientIniFile->readVariable("session","expire"), false);
		$ilAuth->setExpire(0);
		ini_set("session.cookie_lifetime", "0");
//echo "-".get_class($ilAuth)."-";
		$GLOBALS['ilAuth'] =& $ilAuth;
	}
	
	function _getAuthModeOfUser($a_username,$a_password,$a_db_handler = '')
	{
		global $ilDB;
		
		if(isset($_POST['auth_mode']))
		{
			return (int) $_POST['auth_mode'];
		}		


		$db =& $ilDB;
		
		if ($a_db_handler != '')
		{
			$db =& $a_db_handler;
		}
		
		// Is it really necessary to check the auth mode with password ?
		// Changed: smeyer
		$q = "SELECT auth_mode FROM usr_data WHERE ".
			 "login = ".$ilDB->quote($a_username);
			 //"passwd = ".$ilDB->quote(md5($a_password))."";
							 
			 
		$r = $db->query($q);
		$row = $r->fetchRow(DB_FETCHMODE_OBJECT);
//echo "+".$row->auth_mode."+";
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

			case "soap":
				return AUTH_SOAP;
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

			case AUTH_CAS:
				return "cas";
				break;

			case AUTH_SCRIPT:
				return "script";
				break;
				
			case AUTH_SHIBBOLETH:
				return "shibboleth";
				break;

			case AUTH_SOAP:
				return "soap";
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
		include_once('Services/LDAP/classes/class.ilLDAPServer.php');
		if(count(ilLDAPServer::_getActiveServerList()))
		{
			$modes['ldap'] = AUTH_LDAP;			
		}			
		if ($ilias->getSetting("radius_active")) $modes['radius'] = AUTH_RADIUS;
		if ($ilias->getSetting("shib_active")) $modes['shibboleth'] = AUTH_SHIBBOLETH;
		if ($ilias->getSetting("script_active")) $modes['script'] = AUTH_SCRIPT;
		if ($ilias->getSetting("cas_active")) $modes['cas'] = AUTH_CAS;
		if ($ilias->getSetting("soap_auth_active")) $modes['soap'] = AUTH_SOAP;
		return $modes;
	}
	
	function _getAllAuthModes()
	{
		return array(
			AUTH_LOCAL => ilAuthUtils::_getAuthModeName(AUTH_LOCAL),
			AUTH_LDAP => ilAuthUtils::_getAuthModeName(AUTH_LDAP),
			AUTH_SHIBBOLETH => ilAuthUtils::_getAuthModeName(AUTH_SHIBBOLETH),
			AUTH_CAS => ilAuthUtils::_getAuthModeName(AUTH_CAS),
			AUTH_SOAP => ilAuthUtils::_getAuthModeName(AUTH_SOAP),
			AUTH_RADIUS => ilAuthUtils::_getAuthModeName(AUTH_RADIUS));
	}
	
	/**
	* generate free login by starting with a default string and adding
	* postfix numbers
	*/
	function _generateLogin($a_login)
	{
		global $ilDB;
		
		// Check if username already exists
		$found = false;
		$postfix = 0;
		$c_login = $a_login;
		while(!$found)
		{
			$r = $ilDB->query("SELECT login FROM usr_data WHERE login = ".
				$ilDB->quote($c_login));
			if ($r->numRows() > 0)
			{
				$postfix++;
				$c_login = $a_login.$postfix;
			}
			else
			{
				$found = true;
			}
		}
		
		return $c_login;
	}
	
	public static function _hasMultipleAuthenticationMethods()
	{
		include_once('Services/Radius/classes/class.ilRadiusSettings.php');
		
		$rad_settings = ilRadiusSettings::_getInstance();
		if($rad_settings->isActive())
		{
			return true;
		}
		include_once('Services/LDAP/classes/class.ilLDAPServer.php');
		return count(ilLDAPServer::_getActiveServerList()) ? true : false;
	}
	
	public static function _getMultipleAuthModeOptions($lng)
	{
		global $ilSetting;
		
		// in the moment only ldap is activated as additional authentication method
		include_once('Services/LDAP/classes/class.ilLDAPServer.php');
		
		$options[AUTH_LOCAL]['txt'] = $lng->txt('authenticate_ilias');

		// LDAP
		if($ldap_id = ilLDAPServer::_getFirstActiveServer())
		{
			$ldap_server = new ilLDAPServer($ldap_id);
			$options[AUTH_LDAP]['txt'] = $ldap_server->getName();
		}
		include_once('Services/Radius/classes/class.ilRadiusSettings.php');
		$rad_settings = ilRadiusSettings::_getInstance();
		if($rad_settings->isActive())
		{
			$options[AUTH_RADIUS]['txt'] = $rad_settings->getName();
		}
		
		if($ilSetting->get('auth_mode',AUTH_LOCAL) == AUTH_LDAP)
		{
			$default = AUTH_LDAP;
		}
		elseif($ilSetting->get('auth_mode',AUTH_LOCAL) == AUTH_RADIUS)
		{
			$default = AUTH_RADIUS;
		}
		else
		{
			$default = AUTH_LOCAL;
		}
		
		$default = $ilSetting->get('default_auth_mode',$default);
		$default = (int) $_REQUEST['auth_mode'] ? (int) $_REQUEST['auth_mode'] : $default;
		
		$options[$default]['checked'] = true;
		return $options ? $options : array();
	}

	/**
	 * Check if an external account name is required.
	 * That's the case if Radius,LDAP, CAS or SOAP is active
	 *
	 * @access public
	 * @static
	 *
	 * @param
	 */
	public static function _isExternalAccountEnabled()
	{
		global $ilSetting;
		
		if($ilSetting->get("cas_active"))
		{
			return true;
		} 
		if($ilSetting->get("soap_auth_active"))
		{
			return true;
		}
		if($ilSetting->get("shib_active"))
		{
			return true;
		}
		if($ilSetting->get('radius_active'))
		{
			return true;
		}
		include_once('Services/LDAP/classes/class.ilLDAPServer.php');
		if(count(ilLDAPServer::_getActiveServerList()))
		{
			return true;
		}
		return false;
	}
	
	/**
	 * Allow password modification 
	 *
	 * @access public
	 * @static
	 *
	 * @param int auth_mode
	 */
	public static function _allowPasswordModificationByAuthMode($a_auth_mode)
	{
		switch($a_auth_mode)
		{
			case AUTH_LDAP:
			case AUTH_RADIUS:
				return false;
			default:
				return true;
		}
	}
	
	/**
	 * Check if chosen auth mode needs an external account entry
	 *
	 * @access public
	 * @static
	 *
	 * @param int auth_mode
	 */
	public static function _needsExternalAccountByAuthMode($a_auth_mode)
	{
		switch($a_auth_mode)
		{
			case AUTH_LOCAL:
				return false;
			default: 
				return true;
		}
	}
}
?>
