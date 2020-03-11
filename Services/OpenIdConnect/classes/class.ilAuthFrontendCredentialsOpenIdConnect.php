<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilOpenIdConnectSettingsGUI
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 *
 */
class ilAuthFrontendCredentialsOpenIdConnect extends ilAuthFrontendCredentials implements ilAuthCredentials
{
    const SESSION_TARGET = 'oidc_target';

    /**
     * @var ilSetting
     */
    private $settings = null;

    /**
     * @var string
     */
    private $target = null;


    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->settings = ilOpenIdConnectSettings::getInstance();
    }


    /**
     * @return \ilSetting
     */
    protected function getSettings()
    {
        return $this->settings;
    }

    /**
     * @return string
     */
    public function getRedirectionTarget()
    {
        return $this->target;
    }

    /**
     * Init credentials from request
     */
    public function initFromRequest()
    {
        $this->setUsername('');
        $this->setPassword('');

        $this->parseRedirectionTarget();
    }

    /**
     *
     */
    protected function parseRedirectionTarget()
    {
        global $DIC;

        $logger = $DIC->logger()->auth();
        if (!empty($_GET['target'])) {
           $this->target = $_GET['target'];
           \ilSession::set(self::SESSION_TARGET, $this->target);
        }
        elseif (ilSession::get(self::SESSION_TARGET)) {
           $this->target = \ilSession::get(self::SESSION_TARGET);
        }
    }
}
