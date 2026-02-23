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

class ilAuthFrontendCredentialsApache extends ilAuthFrontendCredentials
{
    private readonly \ILIAS\HTTP\GlobalHttpState $http;
    private readonly \ILIAS\Refinery\Factory $refinery;
    private readonly ilCtrlInterface $ctrl;
    private readonly ilSetting $settings;

    public function __construct(
        \ILIAS\HTTP\GlobalHttpState $http,
        \ILIAS\Refinery\Factory $refinery,
        ilCtrlInterface $ctrl
    ) {
        global $DIC;
        $this->http = $http;
        $this->refinery = $refinery;
        $this->ctrl = $ctrl;
        $this->settings = new ilSetting('apache_auth');
        parent::__construct();
    }

    /**
     * Check if an authentication attempt should be done when login page has been called.
     * Redirects in case no apache authentication has been tried before (GET['passed_sso'])
     */
    public function tryAuthenticationOnLoginPage(): void
    {
        if (!$this->getSettings()->get('apache_enable_auth', '0')) {
            return;
        }

        if (!$this->getSettings()->get('apache_auth_authenticate_on_login_page', '0')) {
            return;
        }

        if (
            (defined('IL_CERT_SSO') && (int) IL_CERT_SSO === 1) ||
            !ilContext::supportsRedirects() ||
            $this->http->wrapper()->query()->has('passed_sso')
        ) {
            return;
        }

        $redirect_url = ilUtil::getHtmlPath('./sso/index.php?force_mode_apache=1');

        if ($this->http->wrapper()->query()->has('target')) {
            $url = (string) ($this->http->request()->getServerParams()['REQUEST_URI'] ?? '');
            if (str_starts_with($url, '/')) {
                $url = substr($url, 1);
            }

            if (!str_starts_with($url, 'http')) {
                $parts = parse_url(ILIAS_HTTP_PATH);
                $url = $parts['scheme'] . '://' . $parts['host'] . '/' . $url;
            }

            $uri = new \ILIAS\Data\URI($url);
            /*
             * If `tryAuthenticationOnLoginPage` is called and a permanent-link "target" is provided,
             * we ensure using `goto.php` as landing page after successful authentication
             */
            $uri = $uri->withPath(str_replace(['login.php', 'ilias.php'], 'goto.php', $uri->getPath()));
            $redirect_url = ilUtil::appendUrlParameterString(
                $redirect_url,
                'r=' . urlencode($this->refinery->uri()->toString()->transform($uri))
            );
        }

        $this->ctrl->redirectToURL($redirect_url);
    }

    protected function getSettings(): ilSetting
    {
        return $this->settings;
    }

    public function initFromRequest(): void
    {
        $mapping_field_name = $this->getSettings()->get('apache_auth_username_direct_mapping_fieldname', '');

        $this->logger->dump($this->http->request()->getServerParams(), ilLogLevel::DEBUG);
        $this->logger->debug($mapping_field_name);

        switch ($this->getSettings()->get('apache_auth_username_config_type')) {
            case ilAuthProviderApache::APACHE_AUTH_TYPE_DIRECT_MAPPING:
                if (isset($this->http->request()->getServerParams()[$mapping_field_name])) {
                    $this->setUsername($this->http->request()->getServerParams()[$mapping_field_name]);
                }
                break;

            case ilAuthProviderApache::APACHE_AUTH_TYPE_BY_FUNCTION:
                $this->setUsername(ApacheCustom::getUsername());
                break;
        }
    }

    public function hasValidTargetUrl(): bool
    {
        $target_url = trim(
            $this->http->wrapper()->query()->retrieve('r', $this->refinery->byTrying([
                $this->refinery->kindlyTo()->string(),
                $this->refinery->always(''),
            ]))
        );
        if ($target_url === '') {
            return false;
        }

        $valid_hosts = [];
        $path = ILIAS_DATA_DIR . '/' . CLIENT_ID . '/apache_auth_allowed_domains.txt';
        if (file_exists($path) && is_readable($path)) {
            foreach (file($path) as $line) {
                if (trim($line)) {
                    $valid_hosts[] = trim($line);
                }
            }
        }

        return (new ilWhiteListUrlValidator($target_url, $valid_hosts))->isValid();
    }

    public function getTargetUrl(): string
    {
        $target_url = trim($this->http->wrapper()->query()->retrieve('r', $this->refinery->kindlyTo()->string()));

        return ilUtil::appendUrlParameterString($target_url, 'passed_sso=1');
    }
}
