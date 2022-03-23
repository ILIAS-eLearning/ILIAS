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

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilAuthFrontendCredentialsOpenIdConnect extends ilAuthFrontendCredentials
{
    private const SESSION_TARGET = 'oidc_target';

    private ilOpenIdConnectSettings $settings;
    private ?string $target = null;

    public function __construct()
    {
        parent::__construct();
        $this->settings = ilOpenIdConnectSettings::getInstance();
    }

    protected function getSettings() : ilOpenIdConnectSettings
    {
        return $this->settings;
    }

    public function getRedirectionTarget() : string
    {
        return $this->target;
    }

    public function initFromRequest() : void
    {
        $this->setUsername('');
        $this->setPassword('');

        $this->parseRedirectionTarget();
    }

    protected function parseRedirectionTarget() : void
    {
        if (!empty($_GET['target'])) { // TODO PHP8-REVIEW Please eliminate this.
            $this->target = $_GET['target']; // TODO PHP8-REVIEW Please eliminate this.
            ilSession::set(self::SESSION_TARGET, $this->target);
        } elseif (ilSession::get(self::SESSION_TARGET)) {
            $this->target = ilSession::get(self::SESSION_TARGET);
        }
    }
}
