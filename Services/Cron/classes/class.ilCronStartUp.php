<?php declare(strict_types=1);

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Handles cron (cli) request
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilCronStartUp
{
    private string $client;
    private string $username;
    private string $password;
    private ilAuthSession $authSession;

    public function __construct(
        string $a_client_id,
        string $a_login,
        string $a_password,
        ?ilAuthSession $authSession = null
    ) {
        $this->client = $a_client_id;
        $this->username = $a_login;
        $this->password = $a_password;

        require_once './Services/Context/classes/class.ilContext.php';
        ilContext::init(ilContext::CONTEXT_CRON);

        // define client
        // @see mantis 20371, to get rid of this, the authencation service has to provide a mechanism to pass the client_id
        $_GET['client_id'] = $this->client;

        require_once './include/inc.header.php';

        if (null === $authSession) {
            global $DIC;
            $authSession = $DIC['ilAuthSession'];
        }
        $this->authSession = $authSession;
    }


    /**
     * Start authentication
     * @throws ilCronException if authentication failed.
     */
    public function authenticate() : bool
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


            case ilAuthStatus::STATUS_AUTHENTICATION_FAILED:
            default:
                throw new ilCronException($status->getTranslatedReason());
        }

        return true;
    }

    public function logout() : void
    {
        ilSession::setClosingContext(ilSession::SESSION_CLOSE_USER);
        $this->authSession->logout();
    }
}
