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

/**
 * Logs the user of a launch in, see lti.php. ILIAS goes on to the object the platform launched, or back to
 * the login page of ilStartUpGUI when the login was not accepted.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilLTILoginGUI implements ilCtrlBaseClassInterface, ilCtrlSecurityInterface
{
    public const string CMD_AUTHENTICATE = 'doLTIAuthentication';

    private readonly ilCtrlInterface $ctrl;
    private readonly ilGlobalTemplateInterface $main_tpl;
    private readonly ilAuthSession $auth_session;
    private readonly ilLanguage $lng;
    private readonly ilLogger $logger;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->auth_session = $DIC['ilAuthSession'];
        $this->lng = $DIC->language();
        $this->logger = $DIC->logger()->forComponent('lti');
    }

    public function getUnsafeGetCommands(): array
    {
        return [];
    }

    /**
     * A platform cannot send the token of ilCtrl.
     */
    public function getSafePostCommands(): array
    {
        return [self::CMD_AUTHENTICATE];
    }

    public function executeCommand(): void
    {
        if ($this->ctrl->getCmd() === self::CMD_AUTHENTICATE) {
            $this->authenticate();
        }

        $this->ctrl->redirectByClass(ilStartUpGUI::class, 'showLoginPage');
    }

    private function authenticate(): void
    {
        $this->logger->debug('Trying lti authentication');

        $credentials = new ilAuthFrontendCredentialsLTI();
        $credentials->initFromRequest();

        $provider_factory = new ilAuthProviderFactory();
        $provider = $provider_factory->getProviderByAuthMode($credentials, ilAuthUtils::AUTH_PROVIDER_LTI);

        $status = ilAuthStatus::getInstance();

        $frontend_factory = new ilAuthFrontendFactory();
        $frontend_factory->setContext(ilAuthFrontendFactory::CONTEXT_STANDARD_FORM);
        $frontend = $frontend_factory->getFrontend(
            $this->auth_session,
            $status,
            $credentials,
            [$provider]
        );
        $frontend->authenticate();

        $lti_target = ilLTIViewGUI::getLaunchTarget();
        if ($lti_target !== '') {
            ilSession::set('orig_request_target', $lti_target);
        }

        switch ($status->getStatus()) {
            case ilAuthStatus::STATUS_AUTHENTICATED:
                ilLTISessionCookie::allowCrossSite();
                // the user of a launch goes to the object the platform launched
                if ($lti_target !== '') {
                    $this->ctrl->redirectToURL($lti_target);
                }
                $this->logger->debug('Authentication successful; Redirecting to starting page.');
                ilInitialisation::redirectToStartingPage();

                // no break
            case ilAuthStatus::STATUS_ACCOUNT_MIGRATION_REQUIRED:
                $this->ctrl->redirectByClass(ilStartUpGUI::class, 'showAccountMigration');

                // no break
            case ilAuthStatus::STATUS_AUTHENTICATION_FAILED:
                $this->main_tpl->setOnScreenMessage('failure', $status->getTranslatedReason(), true);
                $this->ctrl->redirectByClass(ilStartUpGUI::class, 'showLoginPage');
        }

        $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('err_wrong_login'), true);
    }
}
