<?php
use Psr\Http\Message\ServerRequestInterface;

class ilAuthFrontendCredentialsSoap extends ilAuthFrontendCredentials
{
    /** @var ServerRequestInterface */
    private $httpRequest;

    /** @var ilCtrl */
    private $ctrl;

    /** @var ilSetting */
    private $settings;

    /**
     * ilAuthFrontendCredentialsApache constructor.
     * @param ServerRequestInterface $httpRequest
     * @param ilCtrl $ctrl
     * @param ilSetting $settings
     */
    public function __construct(ServerRequestInterface $httpRequest, ilCtrl $ctrl, ilSetting $settings)
    {
        $this->httpRequest = $httpRequest;
        $this->ctrl = $ctrl;
        $this->settings = $settings;
        parent::__construct();
    }

    /**
     * Check if an authentication attempt should be done when login page has been called.
     */
    public function tryAuthenticationOnLoginPage()
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
            return false;
        }

        if (!$this->settings->get('soap_auth_active', false)) {
            return false;
        }

        if (empty($this->getUsername()) || empty($this->getPassword())) {
            return false;
        }

        $this->getLogger()->debug('Using SOAP authentication.');

        $status = ilAuthStatus::getInstance();

        require_once 'Services/SOAPAuth/classes/class.ilAuthProviderSoap.php';
        $provider = new ilAuthProviderSoap($this);

        $frontend_factory = new ilAuthFrontendFactory();
        $frontend_factory->setContext(ilAuthFrontendFactory::CONTEXT_STANDARD_FORM);
        $frontend = $frontend_factory->getFrontend(
            $GLOBALS['DIC']['ilAuthSession'],
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