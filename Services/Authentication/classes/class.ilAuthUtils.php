<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
* static utility functions used to manage authentication modes
*
* @author Sascha Hofmann <saschahofmann@gmx.de>
*/
class ilAuthUtils
{
    public const LOCAL_PWV_FULL = 1;
    public const LOCAL_PWV_NO = 2;
    public const LOCAL_PWV_USER = 3;


    public const AUTH_LOCAL = 1;
    public const AUTH_LDAP = 2;
    public const AUTH_SCRIPT = 4;
    public const AUTH_SHIBBOLETH = 5;
    public const AUTH_CAS = 6;
    public const AUTH_SOAP = 7;
    public const AUTH_HTTP = 8; // Used for WebDAV
    public const AUTH_ECS = 9;

    public const AUTH_APACHE = 11;
    public const AUTH_SAML = 12;

    public const AUTH_OPENID_CONNECT = 15;
    
    //TODO this is not used anywhere, can it be removed
    private const AUTH_INACTIVE = 18;
    
    //TODO this is not used anywhere, can it be removed
    private const AUTH_MULTIPLE = 20;
    
    //TODO this is not used anywhere, can it be removed
    private const AUTH_SESSION = 21;

    public const AUTH_PROVIDER_LTI = 22;
    
    //TODO this is not used anywhere, can it be removed
    private const AUTH_SOAP_NO_ILIAS_USER = -100;
    //TODO this is not used anywhere, can it be removed
    private const AUTH_LDAP_NO_ILIAS_USER = -200;
    
    // apache auhtentication failed...
    // maybe no (valid) certificate or
    // username could not be extracted
    //TODO this is not used anywhere, can it be removed
    private const AUTH_APACHE_FAILED = -500;
    
    //TODO this is not used anywhere, can it be removed
    private const AUTH_SAML_FAILED = -501;
  
    //TODO this is not used anywhere, can it be removed
    private const AUTH_MODE_INACTIVE = -1000;
    
    // an external user cannot be found in ilias, but his email address
    // matches one or more ILIAS users
    //TODO this is not used anywhere, can it be removed?
    private const AUTH_SOAP_NO_ILIAS_USER_BUT_EMAIL = -101;
    //TODO this is not used anywhere, can it be removed?
    private const AUTH_CAS_NO_ILIAS_USER = -90;
    
    // ilUser validation (no login)
    //TODO All these are is not used anywhere, can it be removed?
    private const AUTH_USER_WRONG_IP = -600;
    private const AUTH_USER_INACTIVE = -601;
    private const AUTH_USER_TIME_LIMIT_EXCEEDED = -602;
    private const AUTH_USER_SIMULTANEOUS_LOGIN = -603;

    /**
     * Check if authentication is should be forced.
     */
    public static function isAuthenticationForced() : bool
    {
        //TODO rework forced authentication concept
        global $DIC;
        $query_wrapper = $DIC->http()->wrapper()->query();
        return $query_wrapper->has('ecs_hash') || $query_wrapper->has('ecs_hash_url');
    }

    public static function handleForcedAuthentication() : void
    {
        global $DIC;
        $query_wrapper = $DIC->http()->wrapper()->query();
        $string_refinery = $DIC->refinery()->kindlyTo()->string();
        if ($query_wrapper->has('ecs_hash') || $query_wrapper->has('ecs_hash_url')) {
            $credentials = new ilAuthFrontendCredentials();
            $credentials->setUsername($query_wrapper->retrieve('ecs_login', $string_refinery));
            $credentials->setAuthMode((string) self::AUTH_ECS);
            
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

    /**
     * @return string|int|null
     */
    public static function _getAuthMode(?string $a_auth_mode)
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        if (null === $a_auth_mode) {
            return $ilSetting->get("auth_mode");
        }

        if (strpos($a_auth_mode, '_') !== false) {
            $auth_arr = explode('_', $a_auth_mode);
            $auth_switch = $auth_arr[0];
        } else {
            $auth_switch = $a_auth_mode;
        }
        switch ($auth_switch) {
            case "local":
                return self::AUTH_LOCAL;
                break;
                
            case "ldap":
                return ilLDAPServer::getKeyByAuthMode($a_auth_mode);
                
            case 'lti':
                return ilAuthProviderLTI::getKeyByAuthMode($a_auth_mode);
                
            case "script":
                return self::AUTH_SCRIPT;
                break;
                
            case "shibboleth":
                return self::AUTH_SHIBBOLETH;
                break;

            case 'oidc':
                return self::AUTH_OPENID_CONNECT;
                break;

            case 'saml':
                return ilSamlIdp::getKeyByAuthMode($a_auth_mode);

            case "cas":
                return self::AUTH_CAS;
                break;

            case "soap":
                return self::AUTH_SOAP;
                break;
                
            case 'ecs':
                return self::AUTH_ECS;

            case 'apache':
                return self::AUTH_APACHE;

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
            case self::AUTH_LOCAL:
                return "local";
                break;
                
            case self::AUTH_LDAP:
                // begin-patch ldap_multiple
                return ilLDAPServer::getAuthModeByKey($a_auth_key);
                // end-patch ldap_multiple
                
            case self::AUTH_PROVIDER_LTI:
                return ilAuthProviderLTI::getAuthModeByKey($a_auth_key);

            case self::AUTH_CAS:
                return "cas";
                break;

            case self::AUTH_SCRIPT:
                return "script";
                break;
                
            case self::AUTH_SHIBBOLETH:
                return "shibboleth";
                break;

            case self::AUTH_SAML:
                return ilSamlIdp::getAuthModeByKey($a_auth_key);

            case self::AUTH_SOAP:
                return "soap";
                break;
                
            case self::AUTH_ECS:
                return 'ecs';

            case self::AUTH_APACHE:
                return 'apache';

            case self::AUTH_OPENID_CONNECT:
                return 'oidc';
                break;

            default:
                return "default";
                break;
        }
    }

    /**
     * @return array<string, int|string>
     */
    public static function _getActiveAuthModes() : array
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        $modes = [
            'default' => $ilSetting->get("auth_mode"),
            'local' => self::AUTH_LOCAL
        ];

        foreach (ilLDAPServer::_getActiveServerList() as $sid) {
            $modes['ldap_' . $sid] = (self::AUTH_LDAP . '_' . $sid);
        }
        
        foreach (ilAuthProviderLTI::getAuthModes() as $sid) {
            $modes['lti_' . $sid] = (self::AUTH_PROVIDER_LTI . '_' . $sid);
        }

        if (ilOpenIdConnectSettings::getInstance()->getActive()) {
            $modes['oidc'] = self::AUTH_OPENID_CONNECT;
        }

        if ($ilSetting->get("shib_active")) {
            $modes['shibboleth'] = self::AUTH_SHIBBOLETH;
        }
        if ($ilSetting->get("script_active")) {
            $modes['script'] = self::AUTH_SCRIPT;
        }
        if ($ilSetting->get("cas_active")) {
            $modes['cas'] = self::AUTH_CAS;
        }
        if ($ilSetting->get("soap_auth_active")) {
            $modes['soap'] = self::AUTH_SOAP;
        }
        if ($ilSetting->get("apache_active")) {
            $modes['apache'] = self::AUTH_APACHE;
        }
                
        if (ilECSServerSettings::getInstance()->activeServerExists()) {
            $modes['ecs'] = self::AUTH_ECS;
        }

        foreach (ilSamlIdp::getActiveIdpList() as $idp) {
            $idpId = $idp->getIdpId();
            $modes['saml_' . $idpId] = self::AUTH_SAML . '_' . $idpId;
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

    /**
     * @return array<int|string, string>
     */
    public static function _getAllAuthModes() : array
    {
        $modes = array(
            self::AUTH_LOCAL,
            self::AUTH_LDAP,
            self::AUTH_SHIBBOLETH,
            self::AUTH_SAML,
            self::AUTH_CAS,
            self::AUTH_SOAP,
            self::AUTH_ECS,
            self::AUTH_PROVIDER_LTI,
            self::AUTH_OPENID_CONNECT,
            self::AUTH_APACHE
        );
        $ret = array();
        foreach ($modes as $mode) {
            if ($mode === self::AUTH_PROVIDER_LTI) {
                foreach (ilAuthProviderLTI::getAuthModes() as $sid) {
                    $id = self::AUTH_PROVIDER_LTI . '_' . $sid;
                    $ret[$id] = self::_getAuthModeName($id);
                }
                continue;
            }

            // multi ldap implementation
            if ($mode === self::AUTH_LDAP) {
                foreach (ilLDAPServer::_getServerList() as $ldap_id) {
                    $id = self::AUTH_LDAP . '_' . $ldap_id;
                    $ret[$id] = self::_getAuthModeName($id);
                }
                continue;
            }

            if ($mode === self::AUTH_SAML) {
                foreach (ilSamlIdp::getAllIdps() as $idp) {
                    $id = self::AUTH_SAML . '_' . $idp->getIdpId();
                    $ret[$id] = self::_getAuthModeName($id);
                }
                continue;
            }
            $ret[$mode] = self::_getAuthModeName($mode);
        }
        return $ret;
    }
    
    /**
    * generate free login by starting with a default string and adding
    * postfix numbers
    */
    public static function _generateLogin(string $a_login) : string
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
    
    public static function _hasMultipleAuthenticationMethods() : bool
    {
        if (count(ilLDAPServer::_getActiveServerList())) {
            return true;
        }

        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        if ($ilSetting->get('apache_active')) {
            return true;
        }

        // begin-patch auth_plugin
        foreach (self::getAuthPlugins() as $pl) {
            foreach ($pl->getAuthIds() as $auth_id) {
                if ($pl->getMultipleAuthModeOptions($auth_id)) {
                    return true;
                }
            }
        }
        // end-patch auth_plugin


        return false;
    }

    /**
     * @param ilLanguage $lng
     * @return array<int|string, string>
     */
    public static function _getMultipleAuthModeOptions(ilLanguage $lng) : array
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        $options = [];
        // in the moment only ldap is activated as additional authentication method
        
        $options[self::AUTH_LOCAL]['txt'] = $lng->txt('authenticate_ilias');

        
        foreach (ilLDAPServer::_getActiveServerList() as $sid) {
            $server = ilLDAPServer::getInstanceByServerId($sid);
            $options[self::AUTH_LDAP . '_' . $sid]['txt'] = $server->getName();
        }

        if ($ilSetting->get('apache_active')) {
            global $DIC;

            $lng = $DIC['lng'];
            $apache_settings = new ilSetting('apache_auth');
            $options[self::AUTH_APACHE]['txt'] = $apache_settings->get('name', $lng->txt('apache_auth'));
            $options[self::AUTH_APACHE]['hide_in_ui'] = true;
        }

        if ($ilSetting->get('auth_mode', (string) self::AUTH_LOCAL) === (string) self::AUTH_LDAP) {
            $default = self::AUTH_LDAP;
        } else {
            $default = self::AUTH_LOCAL;
        }
        
        $default = $ilSetting->get('default_auth_mode', (string) $default);

        // begin-patch auth_plugin
        $pls = self::getAuthPlugins();
        foreach ($pls as $pl) {
            $auths = $pl->getAuthIds();
            foreach ($auths as $auth_id) {
                $pl_auth_option = $pl->getMultipleAuthModeOptions($auth_id);
                if ($pl_auth_option) {
                    $options += $pl_auth_option;
                }
            }
        }
        // end-patch auth_plugins

        if (array_key_exists($default, $options)) {
            $options[$default]['checked'] = true;
        }

        return $options;
    }

    /**
     * Check if an external account name is required.
     * That's the case if LDAP, CAS or SOAP is active
     */
    public static function _isExternalAccountEnabled() : bool
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
                if ($pl->isAuthActive($auth_id) && $pl->isExternalAccountNameRequired($auth_id)) {
                    return true;
                }
            }
        }
        // end-path auth_plugin
        
        return false;
    }
    
    /**
     * Allow password modification
     * @param int|string auth_mode
     */
    public static function _allowPasswordModificationByAuthMode($a_auth_mode) : bool
    {
        switch ((int) $a_auth_mode) {
            case self::AUTH_LDAP:
            case self::AUTH_ECS:
            case self::AUTH_PROVIDER_LTI:
            case self::AUTH_OPENID_CONNECT:
                return false;
            default:
                return true;
        }
    }
    
    /**
     * Check if chosen auth mode needs an external account entry
     *
     * @param null|string|int $a_auth_mode auth_mode
     */
    public static function _needsExternalAccountByAuthMode($a_auth_mode) : bool
    {
        switch ($a_auth_mode) {
            case self::AUTH_LOCAL:
            case self::AUTH_APACHE:
                return false;
            default:
                return true;
        }
    }

    /**
     * @return bool
     */
    public static function isPasswordModificationHidden() : bool
    {
        /** @var $ilSetting \ilSetting */
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        return $ilSetting->get('usr_settings_hide_password') || $ilSetting->get('usr_settings_disable_password');
    }
    
    /**
     * Check if local password validation is enabled for a specific auth_mode
     * @param int|string $a_authmode
     * @return bool
     */
    public static function isLocalPasswordEnabledForAuthMode($a_authmode) : bool
    {
        global $DIC;

        $ilSetting = $DIC->settings();

        switch ((int) $a_authmode) {
            // always enabled
            case self::AUTH_LOCAL:
            case self::AUTH_APACHE:
                return true;

            // No local passwords for these auth modes
            case self::AUTH_LDAP:
            case self::AUTH_ECS:
            case self::AUTH_SCRIPT:
            case self::AUTH_PROVIDER_LTI:
            case self::AUTH_OPENID_CONNECT:
                return false;

            case self::AUTH_SAML:
                $idp = ilSamlIdp::getInstanceByIdpId(ilSamlIdp::getIdpIdByAuthMode((string) $a_authmode));
                return $idp->isActive() && $idp->allowLocalAuthentication();

            case self::AUTH_SHIBBOLETH:
                return (bool) $ilSetting->get("shib_auth_allow_local", '0');
            case self::AUTH_SOAP:
                return (bool) $ilSetting->get("soap_auth_allow_local", '0');
            case self::AUTH_CAS:
                return (bool) $ilSetting->get("cas_allow_local", '0');

        }
        return false;
    }



    /**
     * Check if password modification is enabled
     * @param int|string $a_authmode
     * @return bool
     */
    public static function isPasswordModificationEnabled($a_authmode) : bool
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        if (self::isPasswordModificationHidden()) {
            return false;
        }
        
        //TODO fix casting strings like 2_1 (auth_key for first ldap server) to int to get it to 2
        switch ((int) $a_authmode) {
            // No local passwords for these auth modes and default
            default:
            case self::AUTH_LDAP:
            case self::AUTH_ECS:
            case self::AUTH_SCRIPT:
            case self::AUTH_PROVIDER_LTI:
            case self::AUTH_OPENID_CONNECT:
                return false;

            case self::AUTH_SAML:
                $idp = ilSamlIdp::getInstanceByIdpId(ilSamlIdp::getIdpIdByAuthMode((string) $a_authmode));
                return $idp->isActive() && $idp->allowLocalAuthentication();
            
            // Always for and local
            case self::AUTH_LOCAL:
            case self::AUTH_APACHE:
                return true;

            // Read setting:
            case self::AUTH_SHIBBOLETH:
                return $ilSetting->get("shib_auth_allow_local");
            case self::AUTH_SOAP:
                return $ilSetting->get("soap_auth_allow_local");
            case self::AUTH_CAS:
                return $ilSetting->get("cas_allow_local");
        }
    }
    
    /**
     * Check if local password validation is supported
     * @param null|string|int $a_authmode
     * @return
     */
    public static function supportsLocalPasswordValidation($a_authmode) : int
    {
        //TODO fix casting strings like 2_1 (auth_key for first ldap server) to int to get it to 2
        switch ((int) $a_authmode) {
            case self::AUTH_LDAP:
            case self::AUTH_LOCAL:
                return self::LOCAL_PWV_FULL;
            
            case self::AUTH_SHIBBOLETH:
            case self::AUTH_OPENID_CONNECT:
            case self::AUTH_SAML:
            case self::AUTH_SOAP:
            case self::AUTH_CAS:
                if (!self::isPasswordModificationEnabled((int) $a_authmode)) {
                    return self::LOCAL_PWV_NO;
                }
                return self::LOCAL_PWV_USER;
                
            case self::AUTH_PROVIDER_LTI:
            case self::AUTH_ECS:
            case self::AUTH_SCRIPT:
            case self::AUTH_APACHE:
            default:
                return self::LOCAL_PWV_USER;
        }
    }
    
    /**
     * Get active enabled auth plugins
     */
    public static function getAuthPlugins() : \Iterator
    {
        return $GLOBALS['DIC']['component.factory']->getActivePluginsInSlot('authhk');
    }

    public static function getAuthModeTranslation(string $a_auth_key, string $auth_name = '') : ?string
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        //TODO fix casting strings like 2_1 (auth_key for first ldap server) to int to get it to 2
        switch ((int) $a_auth_key) {
            case self::AUTH_LDAP:
                $sid = ilLDAPServer::getServerIdByAuthMode($a_auth_key);
                return ilLDAPServer::getInstanceByServerId($sid)->getName();
                
            case self::AUTH_PROVIDER_LTI:
                $sid = ilAuthProviderLTI::getServerIdByAuthMode($a_auth_key);
                return ilAuthProviderLTI::lookupConsumer($sid);
                

            case self::AUTH_SAML:
                $idp_id = ilSamlIdp::getIdpIdByAuthMode($a_auth_key);
                return ilSamlIdp::getInstanceByIdpId($idp_id)->getEntityId();

            default:
                $lng->loadLanguageModule('auth');
                if (!empty($auth_name)) {
                    return $lng->txt('auth_' . $auth_name);
                }

                return $lng->txt('auth_' . self::_getAuthModeName($a_auth_key));

        }
    }
}
