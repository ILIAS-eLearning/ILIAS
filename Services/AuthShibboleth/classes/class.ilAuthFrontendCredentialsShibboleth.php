<?php

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
 * Description of class class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilAuthFrontendCredentialsShibboleth extends ilAuthFrontendCredentials
{
    private ilSetting $settings;

    public function __construct()
    {
        global $DIC;
        parent::__construct();
        $this->settings = $DIC->settings();
    }

    protected function getSettings() : ilSetting
    {
        return $this->settings;
    }

    public function initFromRequest() : void
    {
        $this->setUsername($this->settings->get('shib_login', ''));
        $this->setPassword('');
    }
}
