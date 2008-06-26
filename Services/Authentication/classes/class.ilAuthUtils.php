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
// BEGIN WebDAV: Add support for HTTP authentication
define ("AUTH_HTTP",8);
// END WebDAV: Add support for HTTP authentication
define ("AUTH_ECS",9);


define ("AUTH_INACTIVE",18);

define('AUTH_MULTIPLE',20);

define('AUTH_SOAP_NO_ILIAS_USER', -100);
define('AUTH_LDAP_NO_ILIAS_USER',-200);
define('AUTH_RADIUS_NO_ILIAS_USER',-300);

define('AUTH_MODE_INACTIVE',-1000);


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
		global $ilAuth, $ilSetting, $ilDB, $ilClientIniFile,$ilBench;
//var_dump($_SESSION);
		$ilBench->start('Auth','initAuth');

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
			if ($_POST['username'] != '' and $_POST['password'] != '' or isset($_GET['ecs_hash']))
			{
				$user_auth_mode = ilAuthUtils::_getAuthModeOfUser($_POST['username'], $_POST['password'], $ilDB);

				if ($user_auth_mode == AUTH_CAS && $ilSetting->get("cas_allow_local"))
				{
					$user_auth_mode = AUTH_LOCAL;
				}
				if ($user_auth_mode == AUTH_SOAP && $ilSetting->get("soap_auth_allow_local"))
				{
					$user_auth_mode = AUTH_LOCAL;
				}
				if ($user_auth_mode == AUTH_SHIBBOLETH && $ilSetting->get("shib_auth_allow_local"))
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
		
	// BEGIN WebDAV: Share session between browser and WebDAV client.
	// The realm is needed to support a common session between Auth_HTTP and Auth.
	// It also helps us to distinguish between parallel sessions run on different clients.
	// Common session only works if we use a common session name starting with "_authhttp".
	// We must use the "_authttp" prefix, because it is hardcoded in the session name of
	// class Auth_HTTP.
	// Whenever we use Auth_HTTP, we need to explicitly switch off "sessionSharing", because
	// it interfers with the session mechanism of the other Auth modules. If we would
	// keep this switched on, then users could steal each others session, which would cause
	// a major security breach.
	// Note: The realm and sessionName used here, must be the same as in 
	//       class ilBaseAuthentication. Otherwise, Soap clients won't be able to log
	//       in to ILIAS.
	$realm = CLIENT_ID;
	//$this->writelog('ilias.php realm='.$realm);
	// END WebDAV: Share session between browser and WebDAV client.

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
					// BEGIN WebDAV: Share session between browser and WebDAV client.
					'sessionName' => "_authhttp".md5($realm),
					// END WebDAV: Share session between browser and WebDAV client.
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
		else if ($ilSetting->get("cas_active") && $_POST['username'] == '')
		{
			include_once("Services/CAS/classes/class.ilCASAuth.php");
			
			if (!is_object($GLOBALS['ilCASAuth']))
			{
				$auth_params = array(
					"server_version" => CAS_VERSION_2_0,
					"server_hostname" => $ilSetting->get("cas_server"),
					"server_port" => $ilSetting->get("cas_port"),
					"server_uri" => $ilSetting->get("cas_uri"),
					// BEGIN PATCH WebDAV: Share session between browser and WebDAV client.
					'sessionName' => "_authhttp".md5($realm)
					// END PATCH WebDAV: Share session between browser and WebDAV client.
					);
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
				// BEGIN WebDAV: Support HTTP authentication for WebDAV clients.				
				if (WebDAV_Authentication == 'HTTP' && include_once("Auth/HTTP.php"))
				{
					$auth_params['sessionName'] = "_authhttp".md5($realm);
					$auth_params['sessionSharing'] = false;
					require_once 'class.ilAuthContainerMDB2.php';
					$authContainerDB = new ilAuthContainerMDB2($auth_params);
					$ilAuth = new Auth_HTTP($authContainerDB, $auth_params,"",false);
					$ilAuth->setRealm($realm);
				}
				else
				{
					$auth_params['sessionName'] = "_authhttp".md5($realm);
					require_once 'class.ilAuthContainerMDB2.php';
					$authContainerDB = new ilAuthContainerMDB2($auth_params);
					$ilAuth = new Auth($authContainerDB, $auth_params,"",false);
				}
				// END WebDAV: Support HTTP authentication for WebDAV clients.				
				break;
			
			case AUTH_LDAP:
				// BEGIN WebDAV: Support HTTP Authentication for WebDAV clients.
				// XXX - ilAuthLDAP and ilAuthHTTPLDAP should follow the PEAR design for Auth
				//       objects consisting of a frontend part and a backend part.
				// XXX - This hole patch should be revised, because WebDAV_Authentication
				//       specifies a frontend part.
				if (WebDAV_Authentication == 'HTTP' && include_once("Auth/HTTP.php"))
				{
        			include_once 'Services/LDAP/classes/class.ilAuthHTTPLDAP.php';
					$auth_params['sessionName'] = "_authhttp".md5($realm);
					$auth_params['sessionSharing'] = false;
        			$ilAuth = new ilAuthHTTPLDAP($auth_params);
					$ilAuth->setRealm($realm);
				}
				else
				{
        			include_once 'Services/LDAP/classes/class.ilAuthLDAP.php';
					$auth_params['sessionName'] = "_authhttp".md5($realm);
        			$ilAuth = new ilAuthLDAP($auth_params);
				}
				// END WebDAV: Support HTTP Authentication for WebDAV clients.
				break;
				
			case AUTH_RADIUS:
				// BEGIN PATCH WebDAV: Support HTTP Authentication for WebDAV clients.
				if (WebDAV_Authentication == 'HTTP' && include_once("Auth/HTTP.php"))
				{
					$auth_params['sessionName'] = "_authhttp".md5($realm);
					$auth_params['sessionSharing'] = false;
					$ilAuth = new Auth_HTTP("RADIUS", $auth_params,"",false);
					$ilAuth->setRealm($realm);
				}
				else
				{
					$auth_params['sessionName'] = "_authhttp".md5($realm);
					$ilAuth = new Auth("RADIUS", $auth_params,"",false);
				}
				// END PATCH WebDAV: Support HTTP Authentication for WebDAV clients.
				break;
			
				
			case AUTH_SHIBBOLETH:
			
				// build option string for SHIB::Auth
				$auth_params = array();
				// BEGIN WebDAV: Support HTTP Authentication for WebDAV clients.
				$auth_params['sessionName'] = "_authhttp".md5($realm);
				// END WebDAV: Support HTTP Authentication for WebDAV clients.
				$ilAuth = new ShibAuth($auth_params,true);
				break;
				
			case AUTH_CAS:
				$ilAuth =& $ilCASAuth;
				$ilAuth->forceCASAuth();
				break;
				
			case AUTH_SOAP:
				$ilAuth =& $ilSOAPAuth;
				break;
				
			case AUTH_MULTIPLE:
				include_once('./Services/Authentication/classes/class.ilAuthMultiple.php');
				$ilAuth = new ilAuthMultiple();
				break;
				
			case AUTH_ECS:
				include_once('./Services/WebServices/ECS/classes/class.ilAuthECS.php');
				$ilAuth = new ilAuthECS($_GET['ecs_hash']);
				break;
				
			case AUTH_INACTIVE:
				include_once('./Services/Authentication/classes/class.ilAuthInactive.php');
				$ilAuth = new ilAuthInactive(AUTH_MODE_INACTIVE);
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
				// BEGIN WebDAV: Support HTTP Authentication for WebDAV clients.
				if (WebDAV_Authentication == 'HTTP' && include_once("Auth/HTTP.php"))
				{
					$auth_params['sessionName'] = "_authhttp".md5($realm);
					$auth_params['sessionSharing'] = false;
					//$ilAuth = new Auth_HTTP("DB", $auth_params,"",false);
					require_once 'class.ilAuthContainerMDB2.php';
					$authContainerDB = new ilAuthContainerMDB2($auth_params);
					$ilAuth = new Auth_HTTP($authContainerDB, $auth_params,"",false);
					$ilAuth->setRealm($realm);
				}
				else
				{
					$auth_params['sessionName'] = "_authhttp".md5($realm);
					require_once 'class.ilAuthContainerMDB2.php';
					$authContainerDB = new ilAuthContainerMDB2($auth_params);
					$ilAuth = new Auth($authContainerDB, $auth_params,"",false);
				}
				// END WebDAV: Support HTTP Authentication for WebDAV clients.
				break;

		}

		$ilAuth->setIdle($ilClientIniFile->readVariable("session","expire"), false);
		$ilAuth->setExpire(0);
		ini_set("session.cookie_lifetime", "0");
//echo "-".get_class($ilAuth)."-";
		$GLOBALS['ilAuth'] =& $ilAuth;
		
		$ilBench->stop('Auth','initAuth');
	}
	
	function _getAuthModeOfUser($a_username,$a_password,$a_db_handler = '')
	{
		global $ilDB;
		
		if(isset($_GET['ecs_hash']))
		{
			return AUTH_ECS;
		}
		if(isset($_POST['auth_mode']))
		{
			return (int) $_POST['auth_mode'];
		}

		include_once('./Services/Authentication/classes/class.ilAuthModeDetermination.php');
		$det = ilAuthModeDetermination::_getInstance();
		
		if(!$det->isManualSelection())
		{
			return AUTH_MULTIPLE;
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

		$auth_mode =  self::_getAuthMode($row->auth_mode,$db);
		
		return in_array($auth_mode,self::_getActiveAuthModes()) ? $auth_mode : AUTH_INACTIVE;
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
				
			case 'ecs':
				return AUTH_ECS;


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
				
			case AUTH_ECS:
				return 'ecs';
				
			default:
				return "default";
				break;	
		}
	}
	
	function _getActiveAuthModes()
	{
		global $ilias,$ilSetting;
		
		$modes = array(
						'default'	=> $ilSetting->get("auth_mode"),
						'local'		=> AUTH_LOCAL
						);
		include_once('Services/LDAP/classes/class.ilLDAPServer.php');
		if(count(ilLDAPServer::_getActiveServerList()))
		{
			$modes['ldap'] = AUTH_LDAP;			
		}			
		if ($ilSetting->get("radius_active")) $modes['radius'] = AUTH_RADIUS;
		if ($ilSetting->get("shib_active")) $modes['shibboleth'] = AUTH_SHIBBOLETH;
		if ($ilSetting->get("script_active")) $modes['script'] = AUTH_SCRIPT;
		if ($ilSetting->get("cas_active")) $modes['cas'] = AUTH_CAS;
		if ($ilSetting->get("soap_auth_active")) $modes['soap'] = AUTH_SOAP;
		
		include_once('./Services/WebServices/ECS/classes/class.ilECSSettings.php');
		
		if(ilECSSettings::_getInstance()->isEnabled())
		{
			$modes['ecs'] = AUTH_ECS;
		}
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
			AUTH_RADIUS => ilAuthUtils::_getAuthModeName(AUTH_RADIUS),
			AUTH_ECS => ilAuthUtils::_getAuthModeName(AUTH_ECS));
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
			case AUTH_ECS:
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