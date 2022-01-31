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
                return new ilAuthProviderLDAP($credentials, $ldap_info[1]);
            
            case AUTH_LOCAL:
                $this->getLogger()->debug('Using local database authentication');
                return new ilAuthProviderDatabase($credentials);
                
            case AUTH_SOAP:
                $this->getLogger()->debug('Using SOAP authentication.');
                return new ilAuthProviderSoap($credentials);
                
            case AUTH_APACHE:
                $this->getLogger()->debug('Using apache authentication.');
                return new ilAuthProviderApache($credentials);

            case AUTH_CAS:
                $this->getLogger()->debug('Using CAS authentication');
                return new ilAuthProviderCAS($credentials);

            case ilAuthUtils::AUTH_RADIUS:
                $this->getLogger()->debug('Using radius authentication.');
                return new ilAuthProviderRadius($credentials);
                
            case AUTH_SHIBBOLETH:
                $this->getLogger()->debug('Using shibboleth authentication.');
                return new ilAuthProviderShibboleth($credentials);
                
            case AUTH_PROVIDER_LTI:
                $this->getLogger()->debug('Using lti provider authentication.');
                return new ilAuthProviderLTI($credentials);

            case AUTH_ECS:
                $this->getLogger()->debug('Using ecs authentication.');
                return new ilAuthProviderECS($credentials);

            case AUTH_SAML:
                $this->getLogger()->debug('Using apache authentication.');
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
