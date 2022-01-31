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
 * Description of class class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilAuthFrontendHTTP extends ilAuthFrontend implements ilAuthFrontendInterface
{
    public function authenticate()
    {
        foreach ($this->getProviders() as $provider) {
            $this->resetStatus();
            
            $this->getLogger()->debug('Trying authentication against: ' . get_class($provider));
            
            $provider->doAuthentication($this->getStatus());
            
            $this->getLogger()->debug('Authentication user id: ' . $this->getStatus()->getAuthenticatedUserId());
            
            switch ($this->getStatus()->getStatus()) {
                case ilAuthStatus::STATUS_AUTHENTICATED:
                    return $this->handleAuthenticationSuccess($provider);
                    
                case ilAuthStatus::STATUS_ACCOUNT_MIGRATION_REQUIRED:
                    $this->getLogger()->notice("Account migration required.");
                    break;
                    
                case ilAuthStatus::STATUS_AUTHENTICATION_FAILED:
                default:
                    $this->getLogger()->debug('Authentication failed against: ' . get_class($provider));
                    break;
            }
        }
        return $this->handleAuthenticationFail();
    }

    /**
     * Draw basic auth
     */
    protected function handleAuthenticationFail()
    {
        header("WWW-Authenticate: Basic realm=\"" . CLIENT_ID . "\"");
        header('HTTP/1.0 401 Unauthorized');
    }
}
