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
    /**
     * @var ilSetting
     */
    private $settings = null;


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
     * Init credentials from request
     */
    public function initFromRequest()
    {
        $this->setUsername('');
        $this->setPassword('');
    }
}
