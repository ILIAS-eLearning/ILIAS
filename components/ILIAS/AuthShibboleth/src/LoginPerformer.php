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

namespace ILIAS\AuthShibboleth;

use ilLogger;
use ilCtrlInterface;
use ilGlobalTemplateInterface;
use ilLanguage;
use ilAuthSession;
use ilAuthFrontendCredentialsShibboleth;
use ilAuthProviderFactory;
use ilAuthUtils;
use ilAuthStatus;
use ilAuthFrontendFactory;
use ilException;
use ilInitialisation;
use ilStartUpGUI;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class LoginPerformer
{
    public function __construct(
        private ilLogger $logger,
        private ilCtrlInterface $ctrl,
        private ilGlobalTemplateInterface $template,
        private ilLanguage $lng,
        private ilAuthSession $auth_ession,
    ) {
    }

    public function doShibbolethAuthentication(): void
    {
        $this->logger->debug('Trying shibboleth authentication');

        $credentials = new ilAuthFrontendCredentialsShibboleth();
        $credentials->initFromRequest();

        $provider_factory = new ilAuthProviderFactory();
        $provider = $provider_factory->getProviderByAuthMode($credentials, ilAuthUtils::AUTH_SHIBBOLETH);

        $status = ilAuthStatus::getInstance();

        $frontend_factory = new ilAuthFrontendFactory();
        $frontend_factory->setContext(ilAuthFrontendFactory::CONTEXT_STANDARD_FORM);
        $frontend = $frontend_factory->getFrontend(
            $this->auth_ession,
            $status,
            $credentials,
            [$provider]
        ) ?? throw new ilException('No frontend found');
        $frontend->authenticate();

        switch ($status->getStatus()) {
            case ilAuthStatus::STATUS_AUTHENTICATED:
                $this->logger->debug('Authentication successful; Redirecting to starting page.');
                ilInitialisation::redirectToStartingPage();

                // no break
            case ilAuthStatus::STATUS_ACCOUNT_MIGRATION_REQUIRED:
                $this->ctrl->redirectByClass([ilStartUpGUI::class], 'showAccountMigration');

                // no break
            case ilAuthStatus::STATUS_AUTHENTICATION_FAILED:
                $this->template->setOnScreenMessage('failure', $status->getTranslatedReason(), true);
                $this->ctrl->redirectByClass([ilStartUpGUI::class], 'showLoginPage');
        }

        $this->template->setOnScreenMessage('failure', $this->lng->txt('err_wrong_login'), true);
        $this->ctrl->setTargetScript('ilias.php');
        $this->ctrl->redirectByClass(ilStartUpGUI::class, 'showLoginPage');
    }
}
