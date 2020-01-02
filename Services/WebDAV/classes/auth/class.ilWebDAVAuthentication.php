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
     * Clients that support sessions in webdav (Tested User Agent in brackets):
     * - Windows Explorer (Microsoft-WebDAV-MiniRedir/10.0.16299)
     * - Nautilus on Ubuntu (gvfs/1.36.1)
     *
     * Clients that do not support sessions in webdav:
     * - Finder on Mac (WebDAVFS/3.0.0 (03008000) Darwin/17.7.0)
     * - Konqueror (Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/534.34 (KHTML, like Gecko) konqueror/5.0.97)
     * - WinSCP (WinSCP/5.15.1 neon/0.30.2)
     *
     * @var array
     */
    protected $session_aware_webdav_clients = [
        "Microsoft-WebDAV-MiniRedir",
        "gvfs"
    ];

    /**
     * @param string $user_agent  User Agent from $_SERVER["HTTP_USER_AGENT"]
     * @return bool
     */
    public function isUserAgentSessionAware(string $user_agent) : bool
    {
        foreach ($this->session_aware_webdav_clients as $webdav_client_name) {
            if (stristr($user_agent, $webdav_client_name)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Gets the given user agent from the request. If user agent is not set -> return an empty string
     * @return string
     */
    protected function getUserAgent() : string
    {
        // is user agent set?
        $user_agent = isset($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : "";

        // is value of user agent a string?
        $user_agent = is_string($user_agent) ? $user_agent : "";

        return $user_agent;
    }

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

        if ($this->isUserAgentSessionAware($this->getUserAgent())) {
            if ($DIC['ilAuthSession']->isAuthenticated() && $DIC->user()->getId() != 0) {
                ilLoggerFactory::getLogger('webdav')->debug('User authenticated through session. UserID = ' . $DIC->user()->getId());
                return true;
            }
        } else {
            ilSession::enableWebAccessWithoutSession(true);
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
        
        switch ($status->getStatus()) {
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
