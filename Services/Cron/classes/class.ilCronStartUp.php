<?php

declare(strict_types=1);

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

class ilCronStartUp
{
    private readonly ilAuthSession $authSession;

    public function __construct(
        private readonly string $client,
        private readonly string $username,
        private readonly string $password,
        ?ilAuthSession $authSession = null
    ) {
        /** @noRector  */
        require_once './Services/Context/classes/class.ilContext.php';
        ilContext::init(ilContext::CONTEXT_CRON);

        // @see mantis 20371: To get rid of this, the authentication service has to provide a mechanism to pass the client_id
        $_GET['client_id'] = $this->client;
        /** @noRector  */
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
    public function authenticate(): bool
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

    public function logout(): void
    {
        ilSession::setClosingContext(ilSession::SESSION_CLOSE_USER);
        $this->authSession->logout();
    }
}
