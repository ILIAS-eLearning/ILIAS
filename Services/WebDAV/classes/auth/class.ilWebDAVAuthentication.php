<?php

/**
 * Class ilWebDAVAuthentication
 *
 * Implements the callback to authenticate users. Is called by the sabreDAV Authentication Plugin
 *
 * @author Raphael Heer <raphael.heer@hslu.ch>
 * $Id$
 */
class ilWebDAVAuthentication
{
    /**
     * Callback function. Identifies user by username and password and returns if authentication was successful
     *
     * @param $a_username
     * @param $a_password
     * @return bool
     */
    public function authenticate($a_username, $a_password)
    {
        global $DIC;

        if($DIC['ilAuthSession']->isAuthenticated() && $DIC->user()->getId() != 0)
        {
            ilLoggerFactory::getLogger('webdav')->debug('User authenticated through session. UserID = ' . $DIC->user()->getId());
            return true;
        }
       
        include_once './Services/Authentication/classes/Frontend/class.ilAuthFrontendCredentialsHTTP.php';
        $credentials = new ilAuthFrontendCredentialsHTTP();
        $credentials->setUsername($a_username);
        $credentials->setPassword($a_password);
        
        include_once './Services/Authentication/classes/Provider/class.ilAuthProviderFactory.php';
        $provider_factory = new ilAuthProviderFactory();
        $providers = $provider_factory->getProviders($credentials);
        
        include_once './Services/Authentication/classes/class.ilAuthStatus.php';
        $status = ilAuthStatus::getInstance();
        
        include_once './Services/Authentication/classes/Frontend/class.ilAuthFrontendFactory.php';
        $frontend_factory = new ilAuthFrontendFactory();
        $frontend_factory->setContext(ilAuthFrontendFactory::CONTEXT_HTTP);
        $frontend = $frontend_factory->getFrontend(
            $DIC['ilAuthSession'],
            $status,
            $credentials,
            $providers
            );

        $frontend->authenticate();
        
        switch($status->getStatus())
        {
            case ilAuthStatus::STATUS_AUTHENTICATED:
                ilLoggerFactory::getLogger('webdav')->debug('User authenticated through basic authentication. UserId = ' . $DIC->user()->getId());
                return true;
                
            case ilAuthStatus::STATUS_ACCOUNT_MIGRATION_REQUIRED:
                ilLoggerFactory::getLogger('webdav')->info('Basic authentication failed; Account migration required.');
                return false;
                
            case ilAuthStatus::STATUS_AUTHENTICATION_FAILED:
                ilLoggerFactory::getLogger('webdav')->info('Basic authentication failed; Wrong login, password.');
                return false;
        }
        
        return false;
    }
}