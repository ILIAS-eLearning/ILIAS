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

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilAuthFrontendHTTP extends ilAuthFrontend implements ilAuthFrontendInterface
{
    private ilLogger $logger;

    public function __construct(ilAuthSession $session, ilAuthStatus $status, ilAuthCredentials $credentials, array $providers)
    {
        parent::__construct($session, $status, $credentials, $providers);

        global $DIC;
        $this->logger = $DIC->logger()->auth();
    }

    public function authenticate(): bool
    {
        foreach ($this->getProviders() as $provider) {
            $this->resetStatus();

            $this->logger->debug('Trying authentication against: ' . get_class($provider));

            $provider->doAuthentication($this->getStatus());

            $this->logger->debug('Authentication user id: ' . $this->getStatus()
                ->getAuthenticatedUserId());

            switch ($this->getStatus()->getStatus()) {
                case ilAuthStatus::STATUS_AUTHENTICATED:
                    return $this->handleAuthenticationSuccess($provider);

                case ilAuthStatus::STATUS_ACCOUNT_MIGRATION_REQUIRED:
                    $this->logger->notice("Account migration required.");
                    break;

                case ilAuthStatus::STATUS_AUTHENTICATION_FAILED:
                default:
                    $this->logger->debug('Authentication failed against: ' . get_class($provider));
                    break;
            }
        }
        return $this->handleAuthenticationFail();
    }

    /**
     * Draw basic auth
     */
    protected function handleAuthenticationFail(): bool
    {
        header("WWW-Authenticate: Basic realm=\"" . CLIENT_ID . "\"");
        header('HTTP/1.0 401 Unauthorized');
        return false;
    }
}
