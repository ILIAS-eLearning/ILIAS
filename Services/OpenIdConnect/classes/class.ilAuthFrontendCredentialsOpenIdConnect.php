<?php

declare(strict_types=1);

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

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilAuthFrontendCredentialsOpenIdConnect extends ilAuthFrontendCredentials
{
    private const SESSION_TARGET = 'oidc_target';
    private const QUERY_PARAM_TARGET = 'target';

    private ilOpenIdConnectSettings $settings;
    private ?string $target = null;

    public function __construct()
    {
        global $DIC;

        parent::__construct();
        $this->settings = ilOpenIdConnectSettings::getInstance();
        $httpquery = $DIC->http()->wrapper()->query();
        if ($httpquery->has(self::QUERY_PARAM_TARGET)) {
            $this->target = $httpquery->retrieve(self::QUERY_PARAM_TARGET, $DIC->refinery()->to()->string());
        }
    }

    protected function getSettings(): ilOpenIdConnectSettings
    {
        return $this->settings;
    }

    public function getRedirectionTarget(): ?string
    {
        return $this->target;
    }

    public function initFromRequest(): void
    {
        $this->setUsername('');
        $this->setPassword('');

        $this->parseRedirectionTarget();
    }

    protected function parseRedirectionTarget(): void
    {
        if ($this->target) {
            ilSession::set(self::SESSION_TARGET, $this->target);
        } elseif (ilSession::get(self::SESSION_TARGET)) {
            $this->target = ilSession::get(self::SESSION_TARGET);
        }
    }
}
