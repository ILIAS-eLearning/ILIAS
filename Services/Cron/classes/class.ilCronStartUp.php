<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Handles cron (cli) request
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilCronStartUp
{
    private $client = '';
    private $username = '';
    private $password = '';

    /** @var ilAuthSession */
    private $authSession;

    /**
     * @param $a_client_id
     * @param $a_login
     * @param $a_password
     * @param ilAuthSession|null $authSession
     */
    public function __construct(
        $a_client_id,
        $a_login,
        $a_password,
        ilAuthSession $authSession = null
    ) {
        $this->client = $a_client_id;
        $this->username = $a_login;
        $this->password = $a_password;

        include_once './Services/Context/classes/class.ilContext.php';
        ilContext::init(ilContext::CONTEXT_CRON);

        // define client
        // @see mantis 20371
        $_GET['client_id'] = $this->client;

        include_once './include/inc.header.php';

        if (null === $authSession) {
            global $DIC;
            $authSession = $DIC['ilAuthSession'];
        }
        $this->authSession = $authSession;
    }
    

    /**
     * Start authentication
     * @return bool
     *
     * @throws ilCronException if authentication failed.
     */
    public function authenticate()
    {
        $credentials = new ilAuthFrontendCredentials();
        $credentials->setUsername($this->username);
        $credentials->setPassword($this->password);
        
        $provider_factory = new ilAuthProviderFactory();
        $providers = $provider_factory->getProviders($credentials);
            
        $status = ilAuthStatus::getInstance();
            
        $frontend_factory = new ilAuthFrontendFactory();
        $frontend_factory->setContext(ilAuthFrontendFactory::CONTEXT_CLI);

        $frontend = $frontend_factory->getFrontend(
            $this->authSession,
            $status,
            $credentials,
            $providers
        );
            
        $frontend->authenticate();
            
        switch ($status->getStatus()) {
            case ilAuthStatus::STATUS_AUTHENTICATED:
                ilLoggerFactory::getLogger('auth')->debug('Authentication successful; Redirecting to starting page.');
                return true;
                

            default:
            case ilAuthStatus::STATUS_AUTHENTICATION_FAILED:
                throw new ilCronException($status->getTranslatedReason());
        }
        return true;
    }

    /**
     * Closes the current auth session
     */
    public function logout()
    {
        ilSession::setClosingContext(ilSession::SESSION_CLOSE_USER);
        $this->authSession->logout();
    }
}
