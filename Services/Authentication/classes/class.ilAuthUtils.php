<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


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
define('AUTH_OPENID',10);

define ("AUTH_APACHE",11);

define ("AUTH_INACTIVE",18);

define('AUTH_MULTIPLE',20);

define('AUTH_SOAP_NO_ILIAS_USER', -100);
define('AUTH_LDAP_NO_ILIAS_USER',-200);
define('AUTH_RADIUS_NO_ILIAS_USER',-300);
define('AUTH_OPENID_NO_ILIAS_USER',-400);

// apache auhtentication failed...
// maybe no (valid) certificate or
// username could not be extracted
define('AUTH_APACHE_FAILED', -500);


define('AUTH_MODE_INACTIVE',-1000);

// an external user cannot be found in ilias, but his email address
// matches one or more ILIAS users
define('AUTH_SOAP_NO_ILIAS_USER_BUT_EMAIL', -101);
define('AUTH_CAS_NO_ILIAS_USER', -90);

// ilUser validation (no login)
define('AUTH_USER_WRONG_IP', -600);
define('AUTH_USER_INACTIVE', -601);
define('AUTH_USER_TIME_LIMIT_EXCEEDED', -602);
define('AUTH_USER_SIMULTANEOUS_LOGIN', -603);
define('AUTH_CAPTCHA_INVALID', -604);


include_once './Services/Authentication/classes/class.ilAuthFactory.php';
require_once('Services/Authentication/classes/class.ilSessionControl.php');


/**
* static utility functions used to manage authentication modes
*
* @author Sascha Hofmann <saschahofmann@gmx.de>
* @version $Id$
*
*/
class ilAuthUtils
{
	const LOCAL_PWV_FULL = 1;
	const LOCAL_PWV_NO = 2;
	const LOCAL_PWV_USER = 3;

	
	/**
	* initialises $ilAuth 
	*/
	function _initAuth()
	{
		global $ilAuth, $ilSetting, $ilDB, $ilClientIniFile,$ilBench;

		$user_auth_mode = false;
		$ilBench->start('Auth','initAuth');


		// get default auth mode 
		//$default_auth_mode = $this->getSetting("auth_mode");
		define ("AUTH_DEFAULT", $ilSetting->get("auth_mode") ? $ilSetting->get("auth_mode") : AUTH_LOCAL);
		
		// determine authentication method if no session is found and username & password is posted
		// does this if statement make any sense? we enter this block nearly everytime.	
		
        if (empty($_SESSION) ||
            (!isset($_SESSION['_authsession']['registered']) ||
             $_SESSION['_authsession']['registered'] !== true))
        {
			// no sesssion found
			if (isset($_POST['username']) and $_POST['username'] != '' and $_POST['password'] != '' or isset($_GET['ecs_hash']) or isset($_GET['ecs_hash_url']) or isset($_POST['oid_username']) or isset($_GET['oid_check_status']))
			{
				$user_auth_mode = ilAuthUtils::_getAuthModeOfUser($_POST['username'], $_POST['password'], $ilDB);
				ilLoggerFactory::getLogger('auth')->debug('Authmode is '. $user_auth_mode);

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
			else if ($_POST['auth_mode'] == AUTH_APACHE)
			{
				$user_auth_mode = AUTH_APACHE;
			}
        }
	
		// to do: other solution?
		if (!$ilSetting->get("soap_auth_active") && $user_auth_mode == AUTH_SOAP)
		{
			$user_auth_mode = AUTH_LOCAL;
		}
		
		if($ilSetting->get("cas_active") && $_GET['forceCASLogin'])
		{
			ilAuthFactory::setContext(ilAuthFactory::CONTEXT_CAS);
			$user_auth_mode = AUTH_CAS;
		}

		if($ilSetting->get("apache_active") && $user_auth_mode == AUTH_APACHE)
		{
			ilAuthFactory::setContext(ilAuthFactory::CONTEXT_APACHE);
			$user_auth_mode = AUTH_APACHE;
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
			
			define('AUTH_CURRENT',AUTH_SOAP);
		}
		// if Shibboleth is active and the user is authenticated
		// we set auth_mode to Shibboleth
		else if (	$ilSetting->get("shib_active")
				&& $_SERVER[$ilSetting->get("shib_login")])
		{
			define ("AUTH_CURRENT", AUTH_SHIBBOLETH);
		}
		else
		{
			define ("AUTH_CURRENT", $user_auth_mode);
		}
//var_dump($_SESSION);

		// Determine the authentication method to use
		if (defined("WebDAV_Authentication") && WebDAV_Authentication == 'HTTP') {
                        // Since WebDAV clients create the login form by 
                        // themselves, we can not provide buttons on the form for 
                        // choosing an authentication method. 
                        // If the user is already logged in, we continue using
                        // the current authentication method. If the user is
                        // not logged in yet, we use the "multiple authentication"
                        // method using a predefined sequence of authentication methods.
			$authmode = AUTH_CURRENT ? AUTH_CURRENT : AUTH_MULTIPLE;
		} 
		else 
		{
			$authmode = AUTH_CURRENT;
		}
//var_dump($authmode);
        // if no auth mode selected AND default mode is AUTH_APACHE then use it...
		if ($authmode == null && AUTH_DEFAULT == AUTH_APACHE)
			$authmode = AUTH_APACHE;

		// begin-patch ldap_multiple
		// we cast to int => AUTH_LDAP_1 matches AUTH_LDAP
		switch ((int) $authmode)
		{
			case AUTH_LDAP:
			
				include_once './Services/LDAP/classes/class.ilLDAPServer.php';
				$sid = ilLDAPServer::getServerIdByAuthMode($authmode);
				include_once './Services/LDAP/classes/class.ilAuthContainerLDAP.php';
				$ilAuth = ilAuthFactory::factory(new ilAuthContainerLDAP($sid));
				break;
				
			case AUTH_RADIUS:

				include_once './Services/Radius/classes/class.ilAuthContainerRadius.php';
				$ilAuth = ilAuthFactory::factory(new ilAuthContainerRadius());
				break;

			case AUTH_SHIBBOLETH:
				// build option string for SHIB::Auth
				$auth_params = array();
				$auth_params['sessionName'] = "_authhttp".md5($realm);
				$ilAuth = new ShibAuth($auth_params,true);
				break;
				
			case AUTH_CAS:

				include_once './Services/CAS/classes/class.ilAuthContainerCAS.php';
				$ilAuth = ilAuthFactory::factory(new ilAuthContainerCAS());
				break;

			case AUTH_SOAP:

				include_once './Services/SOAPAuth/classes/class.ilAuthContainerSOAP.php';
				$ilAuth = ilAuthFactory::factory(new ilAuthContainerSOAP());
				break;
				
			case AUTH_MULTIPLE:

				include_once './Services/Authentication/classes/class.ilAuthContainerMultiple.php';
				$ilAuth = ilAuthFactory::factory(new ilAuthContainerMultiple());
				break;

			case AUTH_ECS:
				include_once './Services/WebServices/ECS/classes/class.ilAuthContainerECS.php';
				$ilAuth = ilAuthFactory::factory(new ilAuthContainerECS());
				break;
				
			case AUTH_OPENID:
				
				include_once './Services/OpenId/classes/class.ilAuthContainerOpenId.php';
				$ilAuth = ilAuthFactory::factory(new ilAuthContainerOpenId());
				break;

			case AUTH_INACTIVE:
				require_once('./Services/Authentication/classes/class.ilAuthInactive.php');
				$ilAuth = new ilAuthInactive(AUTH_MODE_INACTIVE);
				break;

			case AUTH_APACHE:
				include_once './Services/AuthApache/classes/class.ilAuthContainerApache.php';
				ilAuthFactory::setContext(ilAuthFactory::CONTEXT_APACHE);
				$ilAuth = ilAuthFactory::factory(new ilAuthContainerApache());
				break;

			// begin-patch auth_plugin
			case AUTH_LOCAL:
				global $ilLog;
				include_once './Services/Database/classes/class.ilAuthContainerMDB2.php';
				$ilAuth = ilAuthFactory::factory(new ilAuthContainerMDB2());
				break;

			default:
				// check for plugin
				if($authmode)
				{
					foreach(self::getAuthPlugins() as $pl)
					{
						$container = $pl->getContainer($authmode);
						if($container instanceof Auth_Container)
						{
							ilLoggerFactory::getLogger('auth')->info('Using plugin authentication with auth mode ' . $authmode);
							$ilAuth = ilAuthFactory::factory($container);
							break 2;
						}
					}
				}
				#$GLOBALS['ilLog']->write(__METHOD__.' Using default authentication');
				// default for logged in users
				include_once './Services/Database/classes/class.ilAuthContainerMDB2.php';
				$ilAuth = ilAuthFactory::factory(new ilAuthContainerMDB2());
				break;
			// end-patch auth_plugin
		}
		
                // Due to a bug in Pear Auth_HTTP, we can't use idle time 
                // with WebDAV clients. If we used it, users could never log
                // back into ILIAS once their session idled out. :(
		if (!defined("WebDAV_Authentication") || WebDAV_Authentication != 'HTTP')
		{			
			$ilAuth->setIdle(ilSession::getIdleValue(), false);			
		}
		$ilAuth->setExpire(0);

		ini_set("session.cookie_lifetime", "0");
//echo "-".get_class($ilAuth)."-";
		$GLOBALS['ilAuth'] =& $ilAuth;

		ilSessionControl::checkExpiredSession();

		$ilBench->stop('Auth','initAuth');
	}
	
	function _getAuthModeOfUser($a_username,$a_password,$a_db_handler = '')
	{
		global $ilDB;
		
		if(isset($_GET['ecs_hash']) or isset($_GET['ecs_hash_url']))
		{
			ilAuthFactory::setContext(ilAuthFactory::CONTEXT_ECS);
			return AUTH_ECS;
		}
		if(isset($_POST['auth_mode']))
		{
			// begin-patch ldap_multiple
			return $_POST['auth_mode'];
			// end-patch ldap_multiple
		}
		if(isset($_POST['oid_username']) or $_GET['oid_check_status'])
		{
			ilAuthFactory::setContext(ilAuthFactory::CONTEXT_OPENID);
			return AUTH_OPENID;
		}

		include_once('./Services/Authentication/classes/class.ilAuthModeDetermination.php');
		$det = ilAuthModeDetermination::_getInstance();
		
		if(!$det->isManualSelection() and $det->getCountActiveAuthModes() > 1)
		{
			ilLoggerFactory::getLogger('auth')->debug('Using AUTH_MULTIPLE');
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
		global $ilDB, $ilSetting;

		$db =& $ilDB;
		
		if ($a_db_handler != '')
		{
			$db =& $a_db_handler;
		}

		// begin-patch ldap_multiple
		if(strpos($a_auth_mode, '_') !== FALSE)
		{
			$auth_arr = explode('_',$a_auth_mode);
			$auth_switch = $auth_arr[0];
		}
		else
		{
			$auth_switch = $a_auth_mode;
		}
		switch ($auth_switch)
		{
			case "local":
				return AUTH_LOCAL;
				break;
				
			case "ldap":
				// begin-patch ldap_multiple
				include_once './Services/LDAP/classes/class.ilLDAPServer.php';
				return ilLDAPServer::getKeyByAuthMode($a_auth_mode);
				// end-patch ldap_multiple
				
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
				
			case 'openid':
				return AUTH_OPENID;

			case 'apache':
				return AUTH_APACHE;

			default:
				return $ilSetting->get("auth_mode");
				break;	
		}
	}
	
	public static function _getAuthModeName($a_auth_key)
	{
		global $ilias;

		// begin-patch ldap_multiple
		switch ((int) $a_auth_key)
		{
			case AUTH_LOCAL:
				return "local";
				break;
				
			case AUTH_LDAP:
				// begin-patch ldap_multiple
				include_once './Services/LDAP/classes/class.ilLDAPServer.php';
				return ilLDAPServer::getAuthModeByKey($a_auth_key);
				// end-patch ldap_multiple
				
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

			case AUTH_APACHE:
				return 'apache';

			case AUTH_OPENID:
				return 'open_id';
				
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
		// begin-patch ldap_multiple
		foreach(ilLDAPServer::_getActiveServerList() as $sid)
		{
			$modes['ldap_'.$sid] = (AUTH_LDAP.'_'.$sid);
		}
		// end-patch ldap_multiple
		if ($ilSetting->get("radius_active")) $modes['radius'] = AUTH_RADIUS;
		if ($ilSetting->get("shib_active")) $modes['shibboleth'] = AUTH_SHIBBOLETH;
		if ($ilSetting->get("script_active")) $modes['script'] = AUTH_SCRIPT;
		if ($ilSetting->get("cas_active")) $modes['cas'] = AUTH_CAS;
		if ($ilSetting->get("soap_auth_active")) $modes['soap'] = AUTH_SOAP;
		if ($ilSetting->get("apache_active")) $modes['apache'] = AUTH_APACHE;
                
		include_once './Services/WebServices/ECS/classes/class.ilECSServerSettings.php';
		if(ilECSServerSettings::getInstance()->activeServerExists())
		{
			$modes['ecs'] = AUTH_ECS;
		}

		include_once './Services/OpenId/classes/class.ilOpenIdSettings.php';
		if(ilOpenIdSettings::getInstance()->isActive())
		{
			$modes['openid'] = AUTH_OPENID;
		}
		
		// begin-path auth_plugin
		foreach(self::getAuthPlugins() as $pl)
		{
			foreach($pl->getAuthIds() as $auth_id)
			{
				if($pl->isAuthActive($auth_id))
				{
					$modes[$pl->getAuthName($auth_id)] = $auth_id;
				}
			}
		}
		// end-path auth_plugin
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
			AUTH_ECS => ilAuthUtils::_getAuthModeName(AUTH_ECS),
			AUTH_OPENID => ilAuthUtils::_getAuthModeName(AUTH_OPENID),
			AUTH_APACHE => ilAuthUtils::_getAuthModeName(AUTH_APACHE)
		);
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

		if (count(ilLDAPServer::_getActiveServerList()))
			return true;

		global $ilSetting;

		if ($ilSetting->get('apache_active')) {
			return true;
		}
		
		// begin-patch auth_plugin
		foreach(ilAuthUtils::getAuthPlugins() as $pl)
		{
			foreach($pl->getAuthIds() as $auth_id)
			{
				if($pl->getMultipleAuthModeOptions($auth_id))
				{
					return true;
				}
			}
		}
		// end-patch auth_plugin
		
		
		return false;
	}
	
	public static function _getMultipleAuthModeOptions($lng)
	{
		global $ilSetting;
		
		// in the moment only ldap is activated as additional authentication method
		include_once('Services/LDAP/classes/class.ilLDAPServer.php');
		
		$options[AUTH_LOCAL]['txt'] = $lng->txt('authenticate_ilias');

		
		// begin-patch ldap_multiple
		foreach(ilLDAPServer::_getActiveServerList() as $sid)
		{
			$server = ilLDAPServer::getInstanceByServerId($sid);
			$options[AUTH_LDAP.'_'.$sid]['txt'] = $server->getName();
		}
		// end-patch ldap_multiple
		
		include_once('Services/Radius/classes/class.ilRadiusSettings.php');
		$rad_settings = ilRadiusSettings::_getInstance();
		if($rad_settings->isActive())
		{
			$options[AUTH_RADIUS]['txt'] = $rad_settings->getName();
		}

		if ($ilSetting->get('apache_active'))
		{
			global $lng;
			$apache_settings = new ilSetting('apache_auth');
			$options[AUTH_APACHE]['txt'] = $apache_settings->get('name', $lng->txt('apache_auth'));
			$options[AUTH_APACHE]['hide_in_ui'] = true;
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
		
		
		// begin-patch auth_plugin
		$pls = ilAuthUtils::getAuthPlugins();
		foreach($pls as $pl)
		{
			$auths = $pl->getAuthIds();
			foreach($auths as $auth_id)
			{
				$pl_auth_option = $pl->getMultipleAuthModeOptions($auth_id);
				if($pl_auth_option)
				{
					$options = $options + $pl_auth_option;
				}
			}
		}
		// end-patch auth_plugins
		
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
		include_once './Services/OpenId/classes/class.ilOpenIdSettings.php';
		if(ilOpenIdSettings::getInstance()->isActive())
		{
			return true;
		}
		
		// begin-path auth_plugin
		foreach(self::getAuthPlugins() as $pl)
		{
			foreach($pl->getAuthIds() as $auth_id)
			{
				if($pl->isAuthActive($auth_id) and $pl->isExternalAccountNameRequired($auth_id))
				{
					return true;
				}
			}
		}
		// end-path auth_plugin
		
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
		// begin-patch ldap_multiple
		// cast to int
		switch((int) $a_auth_mode)
		{
			case AUTH_LDAP:
			case AUTH_RADIUS:
			case AUTH_ECS:
			case AUTH_OPENID:
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
			case AUTH_APACHE:
				return false;
			default:
				return true;
		}
	}
	
	/**
	 * Check if password modification is enabled
	 * @param object $a_authmode
	 * @return 
	 */
	public static function isPasswordModificationEnabled($a_authmode)
	{
		global $ilSetting;
		
		if($ilSetting->get('usr_settings_hide_password') or $ilSetting->get('usr_settings_disable_password'))
		{
			return false;
		}
		
		// begin-patch ldap_multiple
		// cast to int
		switch((int) $a_authmode)
		{
			// No local passwords for these auth modes
			case AUTH_LDAP:
			case AUTH_RADIUS:
			case AUTH_ECS:
			case AUTH_SCRIPT:
				return false;
			
			// Always for openid and local
			case AUTH_LOCAL:
			case AUTH_OPENID:
			case AUTH_APACHE:
				return true;

			// Read setting:
			case AUTH_SHIBBOLETH:
				return $ilSetting->get("shib_auth_allow_local");
			case AUTH_SOAP:
				return $ilSetting->get("soap_auth_allow_local");
			case AUTH_CAS:
				return $ilSetting->get("cas_allow_local");
		}
	}
	
	/**
	 * Check if local password validation is supported
	 * @param object $a_authmode
	 * @return 
	 */
	public static function supportsLocalPasswordValidation($a_authmode)
	{
		// begin-patch ldap_multiple
		// cast to int
		switch((int) $a_authmode)
		{
			case AUTH_LDAP:
			case AUTH_LOCAL:
			case AUTH_RADIUS:
				return ilAuthUtils::LOCAL_PWV_FULL;
			
			case AUTH_SHIBBOLETH:
			case AUTH_SOAP:
			case AUTH_CAS:
				if(!ilAuthUtils::isPasswordModificationEnabled($a_authmode))
				{
					return ilAuthUtils::LOCAL_PWV_NO;
				}
				return ilAuthUtils::LOCAL_PWV_USER;
				
			case AUTH_ECS:
			case AUTH_OPENID:
			case AUTH_SCRIPT:
			case AUTH_APACHE:
			default:
				return ilAuthUtils::LOCAL_PWV_USER;
		}
	} 
	
	// begin-patch auth_plugin
	/**
	 * Get active enabled auth plugins
	 * @return ilAuthDefinition
	 */
	public static function getAuthPlugins()
	{
		$pls = $GLOBALS['ilPluginAdmin']->getActivePluginsForSlot(
				IL_COMP_SERVICE,
				'Authentication',
				'authhk'
		);
		$pl_objs = array();
		foreach($pls as $pl)
		{
			$pl_objs[] = $GLOBALS['ilPluginAdmin']->getPluginObject(
					IL_COMP_SERVICE,
					'Authentication',
					'authhk',
					$pl
			);
		}
		return $pl_objs;
	}
	// end-patch auth_plugins
	
	/**
	 * 
	 * @param string $a_authmode
	 */
	public static function getAuthModeTranslation($a_auth_key)
	{
		global $lng;
		
		switch((int) $a_auth_key)
		{
			case AUTH_LDAP:
				include_once './Services/LDAP/classes/class.ilLDAPServer.php';
				$sid = ilLDAPServer::getServerIdByAuthMode($a_auth_key);
				$server = ilLDAPServer::getInstanceByServerId($sid);
				return $server->getName();
				
			default:
				return $lng->txt('auth_'.self::_getAuthModeName($a_auth_key));
		}
	}
}
?>
