<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
* static utility functions used to manage authentication modes
*
* @author Sascha Hofmann <saschahofmann@gmx.de>
*/
class ilAuthUtils
{
    const LOCAL_PWV_FULL = 1;
    const LOCAL_PWV_NO = 2;
    const LOCAL_PWV_USER = 3;

    
    const AUTH_LOCAL = 1;
    const AUTH_LDAP = 2;
    const AUTH_RADIUS = 3;
    const AUTH_SCRIPT = 4;
    const AUTH_SHIBBOLETH = 5;
    const AUTH_CAS = 6;
    const AUTH_SOAP = 7;
    const AUTH_HTTP = 8; // Used for WebDAV
    const AUTH_ECS = 9;
    
    const AUTH_APACHE = 11;
    const AUTH_SAML = 12;
    
    const AUTH_OPENID_CONNECT = 15;
    
    //TODO this is not used anywhere, can it be removed
    const AUTH_INACTIVE = 18;
    
    //TODO this is not used anywhere, can it be removed
    const AUTH_MULTIPLE = 20;
    
    //TODO this is not used anywhere, can it be removed
    const AUTH_SESSION = 21;
    
    const AUTH_PROVIDER_LTI = 22;
    
    //TODO this is not used anywhere, can it be removed
    const AUTH_SOAP_NO_ILIAS_USER = -100;
    //TODO this is not used anywhere, can it be removed
    const AUTH_LDAP_NO_ILIAS_USER = -200;
    //TODO found no more refs to this, check if can go away
    //const AUTH_RADIUS_NO_ILIAS_USER = -300;
    
    // apache auhtentication failed...
    // maybe no (valid) certificate or
    // username could not be extracted
    //TODO this is not used anywhere, can it be removed
    const AUTH_APACHE_FAILED = -500;
    
    //TODO this is not used anywhere, can it be removed
    const AUTH_SAML_FAILED = -501;
  
    //TODO this is not used anywhere, can it be removed
    const AUTH_MODE_INACTIVE = -1000;
    
    // an external user cannot be found in ilias, but his email address
    // matches one or more ILIAS users
    //TODO this is not used anywhere, can it be removed?
    const AUTH_SOAP_NO_ILIAS_USER_BUT_EMAIL = -101;
    //TODO this is not used anywhere, can it be removed?
    const AUTH_CAS_NO_ILIAS_USER = -90;
    
    // ilUser validation (no login)
    //TODO All these are is not used anywhere, can it be removed?
    const AUTH_USER_WRONG_IP = -600;
    const AUTH_USER_INACTIVE = -601;
    const AUTH_USER_TIME_LIMIT_EXCEEDED = -602;
    const AUTH_USER_SIMULTANEOUS_LOGIN = -603;

    /**
     * Check if authentication is should be forced.
     */
    public static function isAuthenticationForced()
    {
        if (isset($_GET['ecs_hash']) or isset($_GET['ecs_hash_url'])) {
            return true;
        }
        return false;
    }

    public static function handleForcedAuthentication()
    {
        if (isset($_GET['ecs_hash']) or isset($_GET['ecs_hash_url'])) {
            $credentials = new ilAuthFrontendCredentials();
            $credentials->setUsername($_GET['ecs_login']);
            $credentials->setAuthMode(self::AUTH_ECS);
            
            $provider_factory = new ilAuthProviderFactory();
            $providers = $provider_factory->getProviders($credentials);
            
            $status = ilAuthStatus::getInstance();
            
            $frontend_factory = new ilAuthFrontendFactory();
            $frontend_factory->setContext(ilAuthFrontendFactory::CONTEXT_STANDARD_FORM);
            $frontend = $frontend_factory->getFrontend(
                $GLOBALS['DIC']['ilAuthSession'],
                $status,
                $credentials,
                $providers
            );
            
            $frontend->authenticate();
            
            switch ($status->getStatus()) {
                case ilAuthStatus::STATUS_AUTHENTICATED:
                    return;
                    
                case ilAuthStatus::STATUS_AUTHENTICATION_FAILED:
                    ilInitialisation::goToPublicSection();
                    return;
            }
        }
    }
    
    public static function _getAuthMode($a_auth_mode)
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        if (strpos($a_auth_mode, '_') !== false) {
            $auth_arr = explode('_', $a_auth_mode);
            $auth_switch = $auth_arr[0];
        } else {
            $auth_switch = $a_auth_mode;
        }
        switch ($auth_switch) {
            case "local":
                return ilAuthUtils::AUTH_LOCAL;
                break;
                
            case "ldap":
                return ilLDAPServer::getKeyByAuthMode($a_auth_mode);
                
            case 'lti':
                return ilAuthProviderLTI::getKeyByAuthMode($a_auth_mode);
                
            case "radius":
                return ilAuthUtils::AUTH_RADIUS;
                break;
                
            case "script":
                return ilAuthUtils::AUTH_SCRIPT;
                break;
                
            case "shibboleth":
                return ilAuthUtils::AUTH_SHIBBOLETH;
                break;

            case 'oidc':
                return ilAuthUtils::AUTH_OPENID_CONNECT;
                break;

            case 'saml':
                return ilSamlIdp::getKeyByAuthMode($a_auth_mode);

            case "cas":
                return ilAuthUtils::AUTH_CAS;
                break;

            case "soap":
                return ilAuthUtils::AUTH_SOAP;
                break;
                
            case 'ecs':
                return ilAuthUtils::AUTH_ECS;

            case 'apache':
                return ilAuthUtils::AUTH_APACHE;

            default:
                return $ilSetting->get("auth_mode");
                break;
        }
    }
    
    /**
     * @param $a_auth_key int|string
     */
    public static function _getAuthModeName($a_auth_key) : string
    {
        switch ($a_auth_key) {
            case ilAuthUtils::AUTH_LOCAL:
                return "local";
                break;
                
            case ilAuthUtils::AUTH_LDAP:
                // begin-patch ldap_multiple
                return ilLDAPServer::getAuthModeByKey($a_auth_key);
                // end-patch ldap_multiple
                
            case ilAuthUtils::AUTH_PROVIDER_LTI:
                return ilAuthProviderLTI::getAuthModeByKey($a_auth_key);
                
            case ilAuthUtils::AUTH_RADIUS:
                return "radius";
                break;

            case ilAuthUtils::AUTH_CAS:
                return "cas";
                break;

            case ilAuthUtils::AUTH_SCRIPT:
                return "script";
                break;
                
            case ilAuthUtils::AUTH_SHIBBOLETH:
                return "shibboleth";
                break;

            case ilAuthUtils::AUTH_SAML:
                return ilSamlIdp::getAuthModeByKey($a_auth_key);

            case ilAuthUtils::AUTH_SOAP:
                return "soap";
                break;
                
            case ilAuthUtils::AUTH_ECS:
                return 'ecs';

            case ilAuthUtils::AUTH_APACHE:
                return 'apache';

            case ilAuthUtils::AUTH_OPENID_CONNECT:
                return 'oidc';
                break;

            default:
                return "default";
                break;
        }
    }
    
    public static function _getActiveAuthModes()
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        
        $modes = array(
                        'default' => $ilSetting->get("auth_mode"),
            'local' => ilAuthUtils::AUTH_LOCAL
                        );
        foreach (ilLDAPServer::_getActiveServerList() as $sid) {
            $modes['ldap_' . $sid] = (ilAuthUtils::AUTH_LDAP . '_' . $sid);
        }
        
        foreach (ilAuthProviderLTI::getAuthModes() as $sid) {
            $modes['lti_' . $sid] = (ilAuthUtils::AUTH_PROVIDER_LTI . '_' . $sid);
        }

        if (ilOpenIdConnectSettings::getInstance()->getActive()) {
            $modes['oidc'] = ilAuthUtils::AUTH_OPENID_CONNECT;
        }

        if ($ilSetting->get("radius_active")) {
            $modes['radius'] = ilAuthUtils::AUTH_RADIUS;
        }
        if ($ilSetting->get("shib_active")) {
            $modes['shibboleth'] = ilAuthUtils::AUTH_SHIBBOLETH;
        }
        if ($ilSetting->get("script_active")) {
            $modes['script'] = ilAuthUtils::AUTH_SCRIPT;
        }
        if ($ilSetting->get("cas_active")) {
            $modes['cas'] = ilAuthUtils::AUTH_CAS;
        }
        if ($ilSetting->get("soap_auth_active")) {
            $modes['soap'] = ilAuthUtils::AUTH_SOAP;
        }
        if ($ilSetting->get("apache_active")) {
            $modes['apache'] = ilAuthUtils::AUTH_APACHE;
        }
                
        if (ilECSServerSettings::getInstance()->activeServerExists()) {
            $modes['ecs'] = ilAuthUtils::AUTH_ECS;
        }

        foreach (ilSamlIdp::getActiveIdpList() as $idp) {
            $modes['saml_' . $idp->getIdpId()] = ilAuthUtils::AUTH_SAML . '_' . $idp->getIdpId();
        }

        // begin-path auth_plugin
        foreach (self::getAuthPlugins() as $pl) {
            foreach ($pl->getAuthIds() as $auth_id) {
                if ($pl->isAuthActive($auth_id)) {
                    $modes[$pl->getAuthName($auth_id)] = $auth_id;
                }
            }
        }
        // end-path auth_plugin
        return $modes;
    }
    
    public static function _getAllAuthModes() : array
    {
        $modes = array(
            ilAuthUtils::AUTH_LOCAL,
            ilAuthUtils::AUTH_LDAP,
            ilAuthUtils::AUTH_SHIBBOLETH,
            ilAuthUtils::AUTH_SAML,
            ilAuthUtils::AUTH_CAS,
            ilAuthUtils::AUTH_SOAP,
            ilAuthUtils::AUTH_RADIUS,
            ilAuthUtils::AUTH_ECS,
            ilAuthUtils::AUTH_PROVIDER_LTI,
            ilAuthUtils::AUTH_OPENID_CONNECT,
            ilAuthUtils::AUTH_APACHE
        );
        $ret = array();
        foreach ($modes as $mode) {
            if ($mode == ilAuthUtils::AUTH_PROVIDER_LTI) {
                foreach (ilAuthProviderLTI::getAuthModes() as $sid) {
                    $id = ilAuthUtils::AUTH_PROVIDER_LTI . '_' . $sid;
                    $ret[$id] = ilAuthUtils::_getAuthModeName($id);
                }
                continue;
            }

            // multi ldap implementation
            if ($mode == ilAuthUtils::AUTH_LDAP) {
                foreach (ilLDAPServer::_getServerList() as $ldap_id) {
                    $id = ilAuthUtils::AUTH_LDAP . '_' . $ldap_id;
                    $ret[$id] = ilAuthUtils::_getAuthModeName($id);
                }
                continue;
            } elseif ($mode == ilAuthUtils::AUTH_SAML) {
                foreach (ilSamlIdp::getAllIdps() as $idp) {
                    $id = ilAuthUtils::AUTH_SAML . '_' . $idp->getIdpId();
                    $ret[$id] = ilAuthUtils::_getAuthModeName($id);
                }
                continue;
            }
            $ret[$mode] = ilAuthUtils::_getAuthModeName($mode);
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
        while (!$found) {
            $r = $ilDB->query("SELECT login FROM usr_data WHERE login = " .
                $ilDB->quote($c_login));
            if ($r->numRows() > 0) {
                $postfix++;
                $c_login = $a_login . $postfix;
            } else {
                $found = true;
            }
        }
        
        return $c_login;
    }
    
    public static function _hasMultipleAuthenticationMethods()
    {
        $rad_settings = ilRadiusSettings::_getInstance();
        if ($rad_settings->isActive()) {
            return true;
        }

        if (count(ilLDAPServer::_getActiveServerList())) {
            return true;
        }

        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        if ($ilSetting->get('apache_active')) {
            return true;
        }
        
        // begin-patch auth_plugin
        foreach (ilAuthUtils::getAuthPlugins() as $pl) {
            foreach ($pl->getAuthIds() as $auth_id) {
                if ($pl->getMultipleAuthModeOptions($auth_id)) {
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
        $options = [];
        // in the moment only ldap is activated as additional authentication method
        
        $options[ilAuthUtils::AUTH_LOCAL]['txt'] = $lng->txt('authenticate_ilias');

        
        foreach (ilLDAPServer::_getActiveServerList() as $sid) {
            $server = ilLDAPServer::getInstanceByServerId($sid);
            $options[ilAuthUtils::AUTH_LDAP . '_' . $sid]['txt'] = $server->getName();
        }
        
        $rad_settings = ilRadiusSettings::_getInstance();
        if ($rad_settings->isActive()) {
            $options[ilAuthUtils::AUTH_RADIUS]['txt'] = $rad_settings->getName();
        }

        if ($ilSetting->get('apache_active')) {
            global $DIC;

            $lng = $DIC['lng'];
            $apache_settings = new ilSetting('apache_auth');
            $options[ilAuthUtils::AUTH_APACHE]['txt'] = $apache_settings->get('name', $lng->txt('apache_auth'));
            $options[ilAuthUtils::AUTH_APACHE]['hide_in_ui'] = true;
        }

        if ($ilSetting->get('auth_mode', (string) ilAuthUtils::AUTH_LOCAL) == (string) ilAuthUtils::AUTH_LDAP) {
            $default = ilAuthUtils::AUTH_LDAP;
        } elseif ($ilSetting->get('auth_mode', (string) ilAuthUtils::AUTH_LOCAL) == (string) ilAuthUtils::AUTH_RADIUS) {
            $default = ilAuthUtils::AUTH_RADIUS;
        } else {
            $default = ilAuthUtils::AUTH_LOCAL;
        }
        
        $default = $ilSetting->get('default_auth_mode', (string) $default);
        $default = (int) ($_REQUEST['auth_mode'] ?? $default);

        // begin-patch auth_plugin
        $pls = ilAuthUtils::getAuthPlugins();
        foreach ($pls as $pl) {
            $auths = $pl->getAuthIds();
            foreach ($auths as $auth_id) {
                $pl_auth_option = $pl->getMultipleAuthModeOptions($auth_id);
                if ($pl_auth_option) {
                    $options = $options + $pl_auth_option;
                }
            }
        }
        // end-patch auth_plugins

        if (array_key_exists($default, $options)) {
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
        
        if ($ilSetting->get("cas_active")) {
            return true;
        }
        if ($ilSetting->get("soap_auth_active")) {
            return true;
        }
        if ($ilSetting->get("shib_active")) {
            return true;
        }
        if ($ilSetting->get('radius_active')) {
            return true;
        }
        if (count(ilLDAPServer::_getActiveServerList())) {
            return true;
        }
        
        if (count(ilAuthProviderLTI::getActiveAuthModes())) {
            return true;
        }
        
        if (count(ilSamlIdp::getActiveIdpList()) > 0) {
            return true;
        }

        if (ilOpenIdConnectSettings::getInstance()->getActive()) {
            return true;
        }

        // begin-path auth_plugin
        foreach (self::getAuthPlugins() as $pl) {
            foreach ($pl->getAuthIds() as $auth_id) {
                if ($pl->isAuthActive($auth_id) and $pl->isExternalAccountNameRequired($auth_id)) {
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
        switch ((int) $a_auth_mode) {
            case ilAuthUtils::AUTH_LDAP:
            case ilAuthUtils::AUTH_RADIUS:
            case ilAuthUtils::AUTH_ECS:
            case ilAuthUtils::AUTH_PROVIDER_LTI:
            case ilAuthUtils::AUTH_OPENID_CONNECT:
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
        switch ($a_auth_mode) {
            case ilAuthUtils::AUTH_LOCAL:
            case ilAuthUtils::AUTH_APACHE:
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

        switch ((int) $a_authmode) {
            // always enabled
            case ilAuthUtils::AUTH_LOCAL:
            case ilAuthUtils::AUTH_APACHE:
                return true;

            // No local passwords for these auth modes
            case ilAuthUtils::AUTH_LDAP:
            case ilAuthUtils::AUTH_RADIUS:
            case ilAuthUtils::AUTH_ECS:
            case ilAuthUtils::AUTH_SCRIPT:
            case ilAuthUtils::AUTH_PROVIDER_LTI:
            case ilAuthUtils::AUTH_OPENID_CONNECT:
                return false;

            case ilAuthUtils::AUTH_SAML:
                $idp = ilSamlIdp::getInstanceByIdpId(ilSamlIdp::getIdpIdByAuthMode($a_authmode));
                return $idp->isActive() && $idp->allowLocalAuthentication();

            case ilAuthUtils::AUTH_SHIBBOLETH:
                return $ilSetting->get("shib_auth_allow_local");
            case ilAuthUtils::AUTH_SOAP:
                return $ilSetting->get("soap_auth_allow_local");
            case ilAuthUtils::AUTH_CAS:
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
        switch ((int) $a_authmode) {
            // No local passwords for these auth modes
            case ilAuthUtils::AUTH_LDAP:
            case ilAuthUtils::AUTH_RADIUS:
            case ilAuthUtils::AUTH_ECS:
            case ilAuthUtils::AUTH_SCRIPT:
            case ilAuthUtils::AUTH_PROVIDER_LTI:
            case ilAuthUtils::AUTH_OPENID_CONNECT:
                return false;

            case ilAuthUtils::AUTH_SAML:
                $idp = ilSamlIdp::getInstanceByIdpId(ilSamlIdp::getIdpIdByAuthMode($a_authmode));
                return $idp->isActive() && $idp->allowLocalAuthentication();
            
            // Always for and local
            case ilAuthUtils::AUTH_LOCAL:
            case ilAuthUtils::AUTH_APACHE:
                return true;

            // Read setting:
            case ilAuthUtils::AUTH_SHIBBOLETH:
                return $ilSetting->get("shib_auth_allow_local");
            case ilAuthUtils::AUTH_SOAP:
                return $ilSetting->get("soap_auth_allow_local");
            case ilAuthUtils::AUTH_CAS:
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
        switch ((int) $a_authmode) {
            case ilAuthUtils::AUTH_LDAP:
            case ilAuthUtils::AUTH_LOCAL:
            case ilAuthUtils::AUTH_RADIUS:
                return ilAuthUtils::LOCAL_PWV_FULL;
            
            case ilAuthUtils::AUTH_SHIBBOLETH:
            case ilAuthUtils::AUTH_OPENID_CONNECT:
            case ilAuthUtils::AUTH_SAML:
            case ilAuthUtils::AUTH_SOAP:
            case ilAuthUtils::AUTH_CAS:
                if (!ilAuthUtils::isPasswordModificationEnabled($a_authmode)) {
                    return ilAuthUtils::LOCAL_PWV_NO;
                }
                return ilAuthUtils::LOCAL_PWV_USER;
                
            case ilAuthUtils::AUTH_PROVIDER_LTI:
            case ilAuthUtils::AUTH_ECS:
            case ilAuthUtils::AUTH_SCRIPT:
            case ilAuthUtils::AUTH_APACHE:
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
        return $GLOBALS['DIC']['component.factory']->getActivePluginsInSlot('authhk');
    }
    // end-patch auth_plugins
    
    /**
     * @param string $a_auth_key
     * @param string $auth_name
     */
    public static function getAuthModeTranslation($a_auth_key, $auth_name = '')
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        switch ((int) $a_auth_key) {
            case ilAuthUtils::AUTH_LDAP:
                $sid = ilLDAPServer::getServerIdByAuthMode($a_auth_key);
                $server = ilLDAPServer::getInstanceByServerId($sid);
                return $server->getName();
                
            case ilAuthUtils::AUTH_PROVIDER_LTI:
                $sid = ilAuthProviderLTI::getServerIdByAuthMode($a_auth_key);
                return ilAuthProviderLTI::lookupConsumer($sid);
                

            case ilAuthUtils::AUTH_SAML:
                $idp_id = ilSamlIdp::getIdpIdByAuthMode($a_auth_key);
                $idp = ilSamlIdp::getInstanceByIdpId($idp_id);
                return $idp->getEntityId();

            default:
                $lng->loadLanguageModule('auth');
                if (!empty($auth_name)) {
                    return $lng->txt('auth_' . $auth_name);
                } else {
                    return $lng->txt('auth_' . self::_getAuthModeName($a_auth_key));
                }

        }
    }
}
