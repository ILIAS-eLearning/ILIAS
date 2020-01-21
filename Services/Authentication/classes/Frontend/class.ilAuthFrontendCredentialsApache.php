<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

use Psr\Http\Message\ServerRequestInterface;

/**
 * Description of class class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @author Michael Jansen <mjansen@databay.de>
 *
 */
class ilAuthFrontendCredentialsApache extends ilAuthFrontendCredentials implements ilAuthCredentials
{
    /** @var ServerRequestInterface */
    private $httpRequest;

    /** @var \ilCtrl */
    private $ctrl;
    
    private $settings = null;

    /**
     * ilAuthFrontendCredentialsApache constructor.
     * @param ServerRequestInterface $httpRequest
     * @param \ilCtrl                $ctrl
     */
    public function __construct(ServerRequestInterface $httpRequest, \ilCtrl $ctrl)
    {
        $this->httpRequest = $httpRequest;
        $this->ctrl = $ctrl;
        parent::__construct();

        $this->settings = new \ilSetting('apache_auth');
    }
    
    /**
     * Check if an authentication attempt should be done when login page has been called.
     * Redirects in case no apache authentication has been tried before (GET['passed_sso'])
     */
    public function tryAuthenticationOnLoginPage()
    {
        $cmd = (string) ($this->httpRequest->getQueryParams()['cmd'] ?? '');
        if ('' === $cmd) {
            $cmd = (string) ($this->httpRequest->getParsedBody()['cmd'] ?? '');
        }

        if ('force_login' === $cmd) {
            return false;
        }

        if (!$this->getSettings()->get('apache_enable_auth', false)) {
            return false;
        }

        if (!$this->getSettings()->get('apache_auth_authenticate_on_login_page', false)) {
            return false;
        }

        if (
            !\ilContext::supportsRedirects() ||
            isset($this->httpRequest->getQueryParams()['passed_sso']) ||
            (defined('IL_CERT_SSO') && IL_CERT_SSO == '1')
        ) {
            return false;
        }

        $path = (string) ($this->httpRequest->getServerParams()['REQUEST_URI'] ?? '');
        if (substr($path, 0, 1) === '/') {
            $path = substr($path, 1);
        }

        if (substr($path, 0, 4) !== 'http') {
            $parts = parse_url(ILIAS_HTTP_PATH);
            $path  = $parts['scheme'] . '://' . $parts['host'] . '/' . $path;
        }

        $this->ctrl->redirectToURL(
            \ilUtil::getHtmlPath(
                './sso/index.php?force_mode_apache=1&' .
                'r=' . urlencode($path) .
                '&cookie_path=' . urlencode(IL_COOKIE_PATH) .
                '&ilias_path=' . urlencode(ILIAS_HTTP_PATH)
            )
        );
    }

    /**
     * @return \ilSetting
     */
    protected function getSettings() : \ilSetting
    {
        return $this->settings;
    }

    /**
     * Init credentials from request
     */
    public function initFromRequest()
    {
        $mappingFieldName = $this->getSettings()->get('apache_auth_username_direct_mapping_fieldname', '');

        $this->getLogger()->dump($this->httpRequest->getServerParams(), \ilLogLevel::DEBUG);
        $this->getLogger()->debug($mappingFieldName);

        switch ($this->getSettings()->get('apache_auth_username_config_type')) {
            case \ilAuthProviderApache::APACHE_AUTH_TYPE_DIRECT_MAPPING:
                if (isset($this->httpRequest->getServerParams()[$mappingFieldName])) {
                    $this->setUsername($this->httpRequest->getServerParams()[$mappingFieldName]);
                }
                break;

            case \ilAuthProviderApache::APACHE_AUTH_TYPE_BY_FUNCTION:
                $this->setUsername((string) \ApacheCustom::getUsername());
                break;
        }
    }

    /**
     * @return bool
     */
    public function hasValidTargetUrl() : bool
    {
        $targetUrl = trim((string) ($this->httpRequest->getQueryParams()['r'] ?? ''));
        if (0 == strlen($targetUrl)) {
            return false;
        }

        $validDomains = array();
        $path         = ILIAS_DATA_DIR . '/' . CLIENT_ID . '/apache_auth_allowed_domains.txt';
        if (file_exists($path) && is_readable($path)) {
            foreach (file($path) as $line) {
                if (trim($line)) {
                    $validDomains[] = trim($line);
                }
            }
        }

        $validator = new \ilWhiteListUrlValidator($targetUrl, $validDomains);

        return $validator->isValid();
    }

    /**
     * @return string
     */
    public function getTargetUrl() : string
    {
        return \ilUtil::appendUrlParameterString(trim($this->httpRequest->getQueryParams()['r']), 'passed_sso=1');
    }
}
