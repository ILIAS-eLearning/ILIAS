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
 * Auth provider factory
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilAuthProviderFactory
{
    private ilLogger $logger;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;
        $this->logger = $DIC->logger()->auth();
    }
    
    /**
     * Get provider
     */
    public function getProviders(ilAuthCredentials $credentials) : array
    {
        // Fixed provider selection;
        if ($credentials->getAuthMode() !== '') {
            $this->logger->debug('Returning fixed provider for auth mode: ' . $credentials->getAuthMode());
            return array(
                $this->getProviderByAuthMode($credentials, $credentials->getAuthMode())
            );
        }
        
        $auth_determination = ilAuthModeDetermination::_getInstance();
        $sequence = $auth_determination->getAuthModeSequence($credentials->getUsername());
        
        $providers = array();
        foreach ($sequence as $position => $authmode) {
            $provider = $this->getProviderByAuthMode($credentials, $authmode);
            if ($provider instanceof ilAuthProviderInterface) {
                $providers[] = $provider;
            }
        }
        return $providers;
    }
    
    /**
     * Get provider by auth mode
     */
    public function getProviderByAuthMode(ilAuthCredentials $credentials, $a_authmode) : ?ilAuthProviderInterface
    {
        switch ((int) $a_authmode) {
            case ilAuthUtils::AUTH_LDAP:
                $ldap_info = explode('_', $a_authmode);
                $this->logger->debug('Using ldap authentication with credentials ');
                return new ilAuthProviderLDAP($credentials, (int) $ldap_info[1]);
            
            case ilAuthUtils::AUTH_LOCAL:
                $this->logger->debug('Using local database authentication');
                return new ilAuthProviderDatabase($credentials);
                
            case ilAuthUtils::AUTH_SOAP:
                $this->logger->debug('Using SOAP authentication.');
                return new ilAuthProviderSoap($credentials);
                
            case ilAuthUtils::AUTH_APACHE:
                $this->logger->debug('Using apache authentication.');
                return new ilAuthProviderApache($credentials);

            case ilAuthUtils::AUTH_CAS:
                $this->logger->debug('Using CAS authentication');
                return new ilAuthProviderCAS($credentials);
                
            case ilAuthUtils::AUTH_SHIBBOLETH:
                $this->logger->debug('Using shibboleth authentication.');
                return new ilAuthProviderShibboleth($credentials);
                
            case ilAuthUtils::AUTH_PROVIDER_LTI:
                $this->logger->debug('Using lti provider authentication.');
                return new ilAuthProviderLTI($credentials);

            case ilAuthUtils::AUTH_ECS:
                $this->logger->debug('Using ecs authentication.');
                return new ilAuthProviderECS($credentials);

            case ilAuthUtils::AUTH_SAML:
                $this->logger->debug('Using apache authentication.');
                return new ilAuthProviderSaml($credentials, ilSamlIdp::getIdpIdByAuthMode($a_authmode));

            case ilAuthUtils::AUTH_OPENID_CONNECT:
                $this->logger->debug('Using openid connect authentication.');
                return new ilAuthProviderOpenIdConnect($credentials);

            default:
                $this->logger->debug('Plugin authentication: ' . $a_authmode);
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
