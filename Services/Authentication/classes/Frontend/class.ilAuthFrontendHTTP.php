<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Authentication/classes/Frontend/class.ilAuthFrontend.php';
include_once './Services/Authentication/interfaces/interface.ilAuthFrontendInterface.php';

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
