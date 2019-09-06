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

define ("AUTH_APACHE",11);
define ("AUTH_SAML", 12);

define ('AUTH_OPENID_CONNECT',15);

define ("AUTH_INACTIVE",18);

define('AUTH_MULTIPLE',20);

define ('AUTH_SESSION', 21);

define('AUTH_PROVIDER_LTI', 22);

define('AUTH_SOAP_NO_ILIAS_USER', -100);
define('AUTH_LDAP_NO_ILIAS_USER',-200);
define('AUTH_RADIUS_NO_ILIAS_USER',-300);

// apache auhtentication failed...
// maybe no (valid) certificate or
// username could not be extracted
define('AUTH_APACHE_FAILED', -500);
define('AUTH_SAML_FAILED', -501);

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
	 * Initialize session
	 */
	public static function initSession()
	{
		
	}
	
	/**
	 * Check if authentication is should be forced.
	 */
	public static function isAuthenticationForced()
	{
		if(isset($_GET['ecs_hash']) or isset($_GET['ecs_hash_url']))
		{
			return true;
		}
		return false;
	}
	
	public static function handleForcedAuthentication()
	{
		if(isset($_GET['ecs_hash']) or isset($_GET['ecs_hash_url']))
		{
			include_once './Services/Authentication/classes/Frontend/class.ilAuthFrontendCredentials.php';
			$credentials = new ilAuthFrontendCredentials();
			$credentials->setUsername($_GET['ecs_login']);
			$credentials->setAuthMode(AUTH_ECS);
			
			include_once './Services/Authentication/classes/Provider/class.ilAuthProviderFactory.php';
			$provider_factory = new ilAuthProviderFactory();
			$providers = $provider_factory->getProviders($credentials);
			
			include_once './Services/Authentication/classes/class.ilAuthStatus.php';
			$status = ilAuthStatus::getInstance();
			
			include_once './Services/Authentication/classes/Frontend/class.ilAuthFrontendFactory.php';
			$frontend_factory = new ilAuthFrontendFactory();
			$frontend_factory->setContext(ilAuthFrontendFactory::CONTEXT_STANDARD_FORM);
			$frontend = $frontend_factory->getFrontend(
				$GLOBALS['DIC']['ilAuthSession'],
				$status,
				$credentials,
				$providers
			);
			
			$frontend->authenticate();
			
			switch($status->getStatus())
			{
				case ilAuthStatus::STATUS_AUTHENTICATED:
					return;
					
				case ilAuthStatus::STATUS_AUTHENTICATION_FAILED:
					ilInitialisation::goToPublicSection();
					return;
			}
		}
	}
	

	
	static function _getAuthModeOfUser($a_username,$a_password,$a_db_handler = '')
	{
		global $DIC;

		$ilDB = $DIC['ilDB'];
		
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
		$row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
//echo "+".$row->auth_mode."+";

		
		$auth_mode =  self::_getAuthMode($row->auth_mode,$db);
		
		return in_array($auth_mode,self::_getActiveAuthModes()) ? $auth_mode : AUTH_INACTIVE;
	}
	
	static function _getAuthMode($a_auth_mode,$a_db_handler = '')
	{
		global $DIC;

		$ilDB = $DIC['ilDB'];
		$ilSetting = $DIC['ilSetting'];

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
				
			case 'lti':
				include_once './Services/LTI/classes/InternalProvider/class.ilAuthProviderLTI.php';
				return ilAuthProviderLTI::getKeyByAuthMode($a_auth_mode);
				
			case "radius":
				return AUTH_RADIUS;
				break;
				
			case "script":
				return AUTH_SCRIPT;
				break;
				
			case "shibboleth":
				return AUTH_SHIBBOLETH;
				break;

			case 'oidc':
				return AUTH_OPENID_CONNECT;
				break;

			case 'saml':
				require_once 'Services/Saml/classes/class.ilSamlIdp.php';
				return ilSamlIdp::getKeyByAuthMode($a_auth_mode);

			case "cas":
				return AUTH_CAS;
				break;

			case "soap":
				return AUTH_SOAP;
				break;
				
			case 'ecs':
				return AUTH_ECS;

			case 'apache':
				return AUTH_APACHE;

			default:
				return $ilSetting->get("auth_mode");
				break;	
		}
	}
	
	public static function _getAuthModeName($a_auth_key)
	{
		global $DIC;

		$ilias = $DIC['ilias'];

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
				
			case AUTH_PROVIDER_LTI:
				include_once './Services/LTI/classes/InternalProvider/class.ilAuthProviderLTI.php';
				return ilAuthProviderLTI::getAuthModeByKey($a_auth_key);
				
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

			case AUTH_SAML:
				require_once 'Services/Saml/classes/class.ilSamlIdp.php';
				return ilSamlIdp::getAuthModeByKey($a_auth_key);

			case AUTH_SOAP:
				return "soap";
				break;
				
			case AUTH_ECS:
				return 'ecs';

			case AUTH_APACHE:
				return 'apache';

			case AUTH_PROVIDER_LTI:
				return "lti";
				break;

			case AUTH_OPENID_CONNECT:
				return 'oidc';
				break;

			default:
				return "default";
				break;	
		}
	}
	
	static function _getActiveAuthModes()
	{
		global $DIC;

		$ilias = $DIC['ilias'];
		$ilSetting = $DIC['ilSetting'];
		
		$modes = array(
						'default'	=> $ilSetting->get("auth_mode"),
						'local'		=> AUTH_LOCAL
						);
		include_once('Services/LDAP/classes/class.ilLDAPServer.php');
		foreach(ilLDAPServer::_getActiveServerList() as $sid)
		{
			$modes['ldap_'.$sid] = (AUTH_LDAP.'_'.$sid);
		}
		
		include_once './Services/LTI/classes/InternalProvider/class.ilAuthProviderLTI.php';
		foreach(ilAuthProviderLTI::getAuthModes() as $sid)
		{
			$modes['lti_'.$sid] = (AUTH_PROVIDER_LTI.'_'.$sid);
		}

		if(ilOpenIdConnectSettings::getInstance()->getActive())
		{
			$modes['oidc'] = AUTH_OPENID_CONNECT;
		}

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

		require_once 'Services/Saml/classes/class.ilSamlIdp.php';
		foreach(ilSamlIdp::getActiveIdpList() as $idp)
		{
			$modes['saml_'. $idp->getIdpId()] = AUTH_SAML  . '_' . $idp->getIdpId();
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
	
	static function _getAllAuthModes()
	{
		$modes = array(
			AUTH_LOCAL,
			AUTH_LDAP,
			AUTH_SHIBBOLETH,
			AUTH_SAML,
			AUTH_CAS,
			AUTH_SOAP,
			AUTH_RADIUS,
			AUTH_ECS,
			AUTH_PROVIDER_LTI,
			AUTH_OPENID_CONNECT,
			AUTH_APACHE
		);
		$ret = array();
		foreach($modes as $mode)
		{
			if($mode == AUTH_PROVIDER_LTI)
			{
				include_once './Services/LTI/classes/InternalProvider/class.ilAuthProviderLTI.php';
				foreach(ilAuthProviderLTI::getAuthModes() as $sid)
				{
					$id = AUTH_PROVIDER_LTI.'_'.$sid;
					$ret[$id] = ilAuthUtils::_getAuthModeName($id);
				}
				continue;
			}

			// multi ldap implementation
			if($mode == AUTH_LDAP)
			{
				foreach(ilLDAPServer::_getServerList() as $ldap_id)
				{
					$id = AUTH_LDAP . '_' . $ldap_id;
					$ret[$id] = ilAuthUtils::_getAuthModeName($id);
				}
				continue;
			}
			else if($mode == AUTH_SAML)
			{
				require_once 'Services/Saml/classes/class.ilSamlIdp.php';
				foreach(ilSamlIdp::getAllIdps() as $idp)
				{
					$id = AUTH_SAML . '_' . $idp->getIdpId();
					$ret[$id] = ilAuthUtils::_getAuthModeName($id);
				}
				continue;
			}
			$ret[$mode] =  ilAuthUtils::_getAuthModeName($mode);
		}
		return $ret;
	}
	
	/**
	* generate free login by starting with a default string and adding
	* postfix numbers
	*/
	public static function _generateLogin($a_login)
	{
		global $DIC;

		$ilDB = $DIC['ilDB'];
		
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

		global $DIC;

		$ilSetting = $DIC['ilSetting'];

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
		global $DIC;

		$ilSetting = $DIC['ilSetting'];
		
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
			global $DIC;

			$lng = $DIC['lng'];
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

		if(array_key_exists($default, $options))
		{
			$options[$default]['checked'] = true;
		}

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
		global $DIC;

		$ilSetting = $DIC['ilSetting'];
		
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
		
		include_once './Services/LTI/classes/InternalProvider/class.ilAuthProviderLTI.php';
		if(count(ilAuthProviderLTI::getActiveAuthModes()))
		{
			return true;
		}
		
		require_once 'Services/Saml/classes/class.ilSamlIdp.php';
		if(count(ilSamlIdp::getActiveIdpList()) > 0)
		{
			return true;
		}

		if(ilOpenIdConnectSettings::getInstance()->getActive())
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
		switch((int) $a_auth_mode)
		{
			case AUTH_LDAP:
			case AUTH_RADIUS:
			case AUTH_ECS:
			case AUTH_PROVIDER_LTI:
			case AUTH_OPENID_CONNECT:
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
	 * @return bool
	 */
	public static function isPasswordModificationHidden()
	{
		/** @var $ilSetting \ilSetting */
		global $DIC;

		$ilSetting = $DIC['ilSetting'];

		if ($ilSetting->get('usr_settings_hide_password') || $ilSetting->get('usr_settings_disable_password')) {
			return true;
		}

		return false;
	}
	
	/**
	 * Check if local password validation is enabled for a specific auth_mode
	 * @param int $a_authmode
	 * @return bool
	 */
	public static function isLocalPasswordEnabledForAuthMode($a_authmode)
	{
		global $DIC;

		$ilSetting = $DIC->settings();

		switch((int) $a_authmode)
		{
			// always enabled
			case AUTH_LOCAL:
			case AUTH_APACHE:
				return true;

			// No local passwords for these auth modes
			case AUTH_LDAP:
			case AUTH_RADIUS:
			case AUTH_ECS:
			case AUTH_SCRIPT:
			case AUTH_PROVIDER_LTI:
			case AUTH_OPENID_CONNECT:
				return false;

			case AUTH_SAML:
				require_once 'Services/Saml/classes/class.ilSamlIdp.php';
				$idp = ilSamlIdp::getInstanceByIdpId(ilSamlIdp::getIdpIdByAuthMode($a_authmode));
				return $idp->isActive() && $idp->allowLocalAuthentication();

			case AUTH_SHIBBOLETH:
				return $ilSetting->get("shib_auth_allow_local");
			case AUTH_SOAP:
				return $ilSetting->get("soap_auth_allow_local");
			case AUTH_CAS:
				return $ilSetting->get("cas_allow_local");

		}
		return false;
	}



	/**
	 * Check if password modification is enabled
	 * @param int $a_authmode
	 * @return bool
	 */
	public static function isPasswordModificationEnabled($a_authmode)
	{
		global $DIC;

		$ilSetting = $DIC['ilSetting'];

		if (self::isPasswordModificationHidden()) {
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
			case AUTH_PROVIDER_LTI:
			case AUTH_OPENID_CONNECT:
				return false;

			case AUTH_SAML:
				require_once 'Services/Saml/classes/class.ilSamlIdp.php';
				$idp = ilSamlIdp::getInstanceByIdpId(ilSamlIdp::getIdpIdByAuthMode($a_authmode));
				return $idp->isActive() && $idp->allowLocalAuthentication();
			
			// Always for and local
			case AUTH_LOCAL:
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
			case AUTH_OPENID_CONNECT:
			case AUTH_SAML:
			case AUTH_SOAP:
			case AUTH_CAS:
				if(!ilAuthUtils::isPasswordModificationEnabled($a_authmode))
				{
					return ilAuthUtils::LOCAL_PWV_NO;
				}
				return ilAuthUtils::LOCAL_PWV_USER;
				
			case AUTH_PROVIDER_LTI:
			case AUTH_ECS:
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
		$pls = $GLOBALS['DIC']['ilPluginAdmin']->getActivePluginsForSlot(
				IL_COMP_SERVICE,
				'Authentication',
				'authhk'
		);
		$pl_objs = array();
		foreach($pls as $pl)
		{
			$pl_objs[] = $GLOBALS['DIC']['ilPluginAdmin']->getPluginObject(
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
	 * @param string $a_auth_key
	 */
	public static function getAuthModeTranslation($a_auth_key)
	{
		global $DIC;

		$lng = $DIC['lng'];
		
		switch((int) $a_auth_key)
		{
			case AUTH_LDAP:
				include_once './Services/LDAP/classes/class.ilLDAPServer.php';
				$sid = ilLDAPServer::getServerIdByAuthMode($a_auth_key);
				$server = ilLDAPServer::getInstanceByServerId($sid);
				return $server->getName();
				
			case AUTH_PROVIDER_LTI:
				include_once './Services/LTI/classes/InternalProvider/class.ilAuthProviderLTI.php';
				$sid = ilAuthProviderLTI::getServerIdByAuthMode($a_auth_key);
				return ilAuthProviderLTI::lookupConsumer($sid);
				

			case AUTH_SAML:
				require_once 'Services/Saml/classes/class.ilSamlIdp.php';
				$idp_id = ilSamlIdp::getIdpIdByAuthMode($a_auth_key);
				$idp = ilSamlIdp::getInstanceByIdpId($idp_id);
				return $idp->getEntityId();

			default:
				$lng->loadLanguageModule('auth');
				return $lng->txt('auth_'.self::_getAuthModeName($a_auth_key));
		}
	}
}
?>
