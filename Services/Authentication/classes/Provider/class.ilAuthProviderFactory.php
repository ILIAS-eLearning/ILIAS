<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Auth provider factory
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilAuthProviderFactory
{
    private $logger = null;
    
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->logger = ilLoggerFactory::getLogger('auth');
    }
    
    /**
     * Get current logger
     * @return \ilLogger
     */
    public function getLogger()
    {
        return $this->logger;
    }
    
    /**
     * Get provider
     * @param \ilAuthCredentials $credentials
     */
    public function getProviders(ilAuthCredentials $credentials)
    {
        // Fixed provider selection;
        if (strlen($credentials->getAuthMode())) {
            $this->getLogger()->debug('Returning fixed provider for auth mode: ' . $credentials->getAuthMode());
            return array(
                $this->getProviderByAuthMode($credentials, $credentials->getAuthMode())
            );
        }
        
        include_once './Services/Authentication/classes/class.ilAuthModeDetermination.php';
        $auth_determination = ilAuthModeDetermination::_getInstance();
        $sequence = $auth_determination->getAuthModeSequence($credentials->getUsername());
        
        $providers = array();
        foreach ((array) $sequence as $position => $authmode) {
            $provider = $this->getProviderByAuthMode($credentials, $authmode);
            if ($provider instanceof ilAuthProviderInterface) {
                $providers[] = $provider;
            }
        }
        return $providers;
    }
    
    /**
     * Get provider by auth mode
     * @return \ilAuthProvide
     */
    public function getProviderByAuthMode(ilAuthCredentials $credentials, $a_authmode)
    {
        switch ((int) $a_authmode) {
            case AUTH_LDAP:
                $ldap_info = explode('_', $a_authmode);
                $this->getLogger()->debug('Using ldap authentication with credentials ');
                include_once './Services/LDAP/classes/class.ilAuthProviderLDAP.php';
                return new ilAuthProviderLDAP($credentials, $ldap_info[1]);
            
            case AUTH_LOCAL:
                $this->getLogger()->debug('Using local database authentication');
                include_once './Services/Authentication/classes/Provider/class.ilAuthProviderDatabase.php';
                return new ilAuthProviderDatabase($credentials);
                
            case AUTH_SOAP:
                $this->getLogger()->debug('Using SOAP authentication.');
                include_once './Services/SOAPAuth/classes/class.ilAuthProviderSoap.php';
                return new ilAuthProviderSoap($credentials);
                
            case AUTH_APACHE:
                $this->getLogger()->debug('Using apache authentication.');
                include_once './Services/AuthApache/classes/class.ilAuthProviderApache.php';
                return new ilAuthProviderApache($credentials);

            case AUTH_CAS:
                $this->getLogger()->debug('Using CAS authentication');
                include_once './Services/CAS/classes/class.ilAuthProviderCAS.php';
                return new ilAuthProviderCAS($credentials);

            case AUTH_RADIUS:
                $this->getLogger()->debug('Using radius authentication.');
                include_once './Services/Radius/classes/class.ilAuthProviderRadius.php';
                return new ilAuthProviderRadius($credentials);
                
            case AUTH_SHIBBOLETH:
                $this->getLogger()->debug('Using shibboleth authentication.');
                include_once './Services/AuthShibboleth/classes/class.ilAuthProviderShibboleth.php';
                return new ilAuthProviderShibboleth($credentials);
                
            case AUTH_PROVIDER_LTI:
                $this->getLogger()->debug('Using lti provider authentication.');
                include_once './Services/LTI/classes/InternalProvider/class.ilAuthProviderLTI.php';
                return new ilAuthProviderLTI($credentials);

            case AUTH_ECS:
                $this->getLogger()->debug('Using ecs authentication.');
                include_once './Services/WebServices/ECS/classes/class.ilAuthProviderECS.php';
                return new ilAuthProviderECS($credentials);

            case AUTH_SAML:
                $this->getLogger()->debug('Using apache authentication.');
                require_once 'Services/Saml/classes/class.ilAuthProviderSaml.php';
                require_once 'Services/Saml/classes/class.ilSamlIdp.php';
                return new ilAuthProviderSaml($credentials, ilSamlIdp::getIdpIdByAuthMode($a_authmode));

            case AUTH_OPENID_CONNECT:
                $this->getLogger()->debug('Using openid connect authentication.');
                return new ilAuthProviderOpenIdConnect($credentials);

            default:
                $this->getLogger('Plugin authentication: ' . $a_authmode);
                foreach (ilAuthUtils::getAuthPlugins() as $pl) {
                    $provider = $pl->getProvider($credentials, $a_authmode);
                    if ($provider instanceof ilAuthProviderInterface) {
                        return $provider;
                    }
                }
                break;
        }
        return null;
    }
}
