<?php declare(strict_types = 1);

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
 * @author Raphael Heer <raphael.heer@hslu.ch>
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
     * @var string[]
     */
    protected array $session_aware_webdav_clients = [
        "Microsoft-WebDAV-MiniRedir",
        "gvfs"
    ];
    
    protected ilObjUser $user;
    protected ilAuthSession $session;
    
    public function __construct(ilObjUser $user, ilAuthSession $session)
    {
        $this->user = $user;
        $this->session = $session;
    }
    
    protected function isUserAgentSessionAware(string $user_agent) : bool
    {
        foreach ($this->session_aware_webdav_clients as $webdav_client_name) {
            if (stristr($user_agent, $webdav_client_name)) {
                return true;
            }
        }
        return false;
    }
    
    protected function getUserAgent() : string
    {
        $user_agent = $_SERVER["HTTP_USER_AGENT"] ?? "";
        $user_agent = is_string($user_agent) ? $user_agent : "";

        return $user_agent;
    }
    
    public function authenticate(string $a_username, string $a_password) : bool
    {
        if ($this->isUserAgentSessionAware($this->getUserAgent())) {
            if ($this->session->isAuthenticated() && $this->user->getId() != 0) {
                ilLoggerFactory::getLogger('webdav')->debug('User authenticated through session. UserID = ' . $this->user->getId());
                return true;
            }
        } else {
            ilSession::enableWebAccessWithoutSession(true);
        }
       
        $credentials = new ilAuthFrontendCredentialsHTTP();
        $credentials->setUsername($a_username);
        $credentials->setPassword($a_password);
        
        $provider_factory = new ilAuthProviderFactory();
        $providers = $provider_factory->getProviders($credentials);
        
        $status = ilAuthStatus::getInstance();
        
        $frontend_factory = new ilAuthFrontendFactory();
        $frontend_factory->setContext(ilAuthFrontendFactory::CONTEXT_HTTP);
        $frontend = $frontend_factory->getFrontend(
            $this->session,
            $status,
            $credentials,
            $providers
        );

        $frontend->authenticate();
        
        switch ($status->getStatus()) {
            case ilAuthStatus::STATUS_AUTHENTICATED:
                ilLoggerFactory::getLogger('webdav')->debug('User authenticated through basic authentication. UserId = ' . $this->user->getId());
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
