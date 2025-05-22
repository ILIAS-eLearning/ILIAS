<?php

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

declare(strict_types=1);

namespace ILIAS\Cron\CLI;

use ILIAS\Cron\CronException;

class StartUp
{
    private readonly \ilAuthSession $auth_session;
    private bool $is_authenticated = false;

    public function __construct(
        private readonly string $client,
        private readonly string $username,
        ?\ilAuthSession $authSession = null
    ) {
        /** @noRector */
        \ilContext::init(\ilContext::CONTEXT_CRON);

        // TODO @see mantis 20371: To get rid of this, the authentication service has to provide a mechanism to pass the client_id
        $_GET['client_id'] = $this->client;
        \ilInitialisation::initILIAS();

        if ($authSession === null) {
            global $DIC;
            $authSession = $DIC['ilAuthSession'];
        }
        $this->auth_session = $authSession;
    }

    public function authenticate(): bool
    {
        $credentials = new \ilAuthFrontendCredentials();
        $credentials->setUsername($this->username);

        $status = \ilAuthStatus::getInstance();

        $frontend_factory = new \ilAuthFrontendFactory();
        $frontend_factory->setContext(\ilAuthFrontendFactory::CONTEXT_CLI);

        $provider_factory = new \ilAuthProviderCliFactory();

        $frontend = $frontend_factory->getFrontend(
            $this->auth_session,
            $status,
            $credentials,
            $provider_factory->getProviders($credentials)
        );

        $frontend->authenticate();

        switch ($status->getStatus()) {
            case \ilAuthStatus::STATUS_AUTHENTICATED:
                $this->is_authenticated = true;
                \ilLoggerFactory::getLogger('auth')->debug('Authentication successful; Redirecting to starting page.');
                return true;

            case \ilAuthStatus::STATUS_AUTHENTICATION_FAILED:
            default:
                throw new CronException($status->getTranslatedReason());
        }
    }

    public function logout(): void
    {
        if ($this->is_authenticated) {
            \ilSession::setClosingContext(\ilSession::SESSION_CLOSE_USER);
            $this->auth_session->logout();
        }
    }
}
