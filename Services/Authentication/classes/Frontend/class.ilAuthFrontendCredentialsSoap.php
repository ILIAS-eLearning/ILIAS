<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

use Psr\Http\Message\ServerRequestInterface;

class ilAuthFrontendCredentialsSoap extends ilAuthFrontendCredentials
{
    private ServerRequestInterface $httpRequest;

    private ilCtrl $ctrl;

    private ilSetting $settings;
    
    private ilAuthSession $authSession;

    /**
     * ilAuthFrontendCredentialsApache constructor.
     * @param ServerRequestInterface $httpRequest
     * @param ilCtrl $ctrl
     * @param ilSetting $settings
     */
    public function __construct(ServerRequestInterface $httpRequest, ilCtrl $ctrl, ilSetting $settings)
    {
        global $DIC;
        $this->authSession = $DIC['ilAuthSession'];
        $this->httpRequest = $httpRequest;
        $this->ctrl = $ctrl;
        $this->settings = $settings;
        parent::__construct();
    }

    /**
     * Check if an authentication attempt should be done when login page has been called.
     */
    public function tryAuthenticationOnLoginPage() : void
    {
        $cmd = '';
        if (isset($this->httpRequest->getQueryParams()['cmd']) && is_string($this->httpRequest->getQueryParams()['cmd'])) {
            $cmd = $this->httpRequest->getQueryParams()['cmd'];
        }
        if ('' === $cmd) {
            if (isset($this->httpRequest->getParsedBody()['cmd']) && is_string($this->httpRequest->getParsedBody()['cmd'])) {
                $cmd = $this->httpRequest->getParsedBody()['cmd'];
            }
        }

        $passedSso = '';
        if (isset($this->httpRequest->getQueryParams()['passed_sso']) && is_string($this->httpRequest->getQueryParams()['passed_sso'])) {
            $passedSso = $this->httpRequest->getParsedBody()['passed_sso'];
        }

        if ('force_login' === $cmd || !empty($passedSso)) {
            return;
        }

        if (!(bool) $this->settings->get('soap_auth_active', (string) false)) {
            return;
        }

        if (empty($this->getUsername()) || empty($this->getPassword())) {
            return;
        }

        $this->getLogger()->debug('Using SOAP authentication.');

        $status = ilAuthStatus::getInstance();

        $provider = new ilAuthProviderSoap($this);

        $frontend_factory = new ilAuthFrontendFactory();
        $frontend_factory->setContext(ilAuthFrontendFactory::CONTEXT_STANDARD_FORM);
        $frontend = $frontend_factory->getFrontend(
            $this->authSession,
            $status,
            $this,
            [$provider]
        );

        $frontend->authenticate();

        switch ($status->getStatus()) {
            case ilAuthStatus::STATUS_AUTHENTICATED:
                ilLoggerFactory::getLogger('auth')->debug(
                    'Redirecting to default starting page.'
                );
                ilInitialisation::redirectToStartingPage();
                break;

            case ilAuthStatus::STATUS_AUTHENTICATION_FAILED:
                ilUtil::sendFailure($status->getTranslatedReason(), true);
                $this->ctrl->redirectToURL(ilUtil::appendUrlParameterString(
                    $this->ctrl->getLinkTargetByClass('ilStartupGUI', 'showLoginPage', '', false, false),
                    'passed_sso=1'
                ));
                break;
        }
    }
}
