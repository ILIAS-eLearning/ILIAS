<?php declare(strict_types=1);

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

use Psr\Http\Message\ServerRequestInterface;

/**
 * Description of class class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @author Michael Jansen <mjansen@databay.de>
 *
 */
class ilAuthFrontendCredentialsApache extends ilAuthFrontendCredentials
{
    private ServerRequestInterface $httpRequest;
    private ilCtrl $ctrl;
    private ilSetting $settings;
    private ilLogger $logger;

    public function __construct(ServerRequestInterface $httpRequest, ilCtrl $ctrl)
    {
        global $DIC;
        $this->logger = $DIC->logger()->auth();
        $this->httpRequest = $httpRequest;
        $this->ctrl = $ctrl;
        $this->settings = new ilSetting('apache_auth');
        parent::__construct();
    }

    /**
     * Check if an authentication attempt should be done when login page has been called.
     * Redirects in case no apache authentication has been tried before (GET['passed_sso'])
     */
    public function tryAuthenticationOnLoginPage() : void
    {
        $cmd = (string) ($this->httpRequest->getQueryParams()['cmd'] ?? '');
        if ('' === $cmd) {
            $cmd = (string) ($this->httpRequest->getParsedBody()['cmd'] ?? '');
        }

        if ('force_login' === $cmd) {
            return;
        }

        if (!$this->getSettings()->get('apache_enable_auth', '0')) {
            return;
        }

        if (!$this->getSettings()->get('apache_auth_authenticate_on_login_page', '0')) {
            return;
        }

        if (
            (defined('IL_CERT_SSO') && (int) IL_CERT_SSO === 1) ||
            !ilContext::supportsRedirects() ||
            isset($this->httpRequest->getQueryParams()['passed_sso'])
        ) {
            return;
        }

        $path = (string) ($this->httpRequest->getServerParams()['REQUEST_URI'] ?? '');
        if (strpos($path, '/') === 0) {
            $path = substr($path, 1);
        }

        if (strpos($path, 'http') !== 0) {
            $parts = parse_url(ILIAS_HTTP_PATH);
            $path = $parts['scheme'] . '://' . $parts['host'] . '/' . $path;
        }

        $this->ctrl->redirectToURL(
            ilUtil::getHtmlPath(
                './sso/index.php?force_mode_apache=1&' .
                'r=' . urlencode($path) .
                '&cookie_path=' . urlencode(IL_COOKIE_PATH) .
                '&ilias_path=' . urlencode(ILIAS_HTTP_PATH)
            )
        );
    }

    protected function getSettings() : ilSetting
    {
        return $this->settings;
    }

    public function initFromRequest() : void
    {
        $mappingFieldName = $this->getSettings()->get('apache_auth_username_direct_mapping_fieldname', '');

        $this->logger->dump($this->httpRequest->getServerParams(), ilLogLevel::DEBUG);
        $this->logger->debug($mappingFieldName);

        switch ($this->getSettings()->get('apache_auth_username_config_type')) {
            case ilAuthProviderApache::APACHE_AUTH_TYPE_DIRECT_MAPPING:
                if (isset($this->httpRequest->getServerParams()[$mappingFieldName])) {
                    $this->setUsername($this->httpRequest->getServerParams()[$mappingFieldName]);
                }
                break;

            case ilAuthProviderApache::APACHE_AUTH_TYPE_BY_FUNCTION:
                $this->setUsername(ApacheCustom::getUsername());
                break;
        }
    }

    public function hasValidTargetUrl() : bool
    {
        $targetUrl = trim((string) ($this->httpRequest->getQueryParams()['r'] ?? ''));
        if ($targetUrl === '') {
            return false;
        }

        $validDomains = [];
        $path = ILIAS_DATA_DIR . '/' . CLIENT_ID . '/apache_auth_allowed_domains.txt';
        if (file_exists($path) && is_readable($path)) {
            foreach (file($path) as $line) {
                if (trim($line)) {
                    $validDomains[] = trim($line);
                }
            }
        }

        return (new ilWhiteListUrlValidator($targetUrl, $validDomains))->isValid();
    }

    public function getTargetUrl() : string
    {
        return ilUtil::appendUrlParameterString(trim($this->httpRequest->getQueryParams()['r']), 'passed_sso=1');
    }
}
