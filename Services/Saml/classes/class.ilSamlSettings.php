<?php declare(strict_types=1);
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilSamlSettings
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilSamlSettings
{
    protected static ?self $instance = null;
    protected ilSetting $settings;

    protected function __construct()
    {
        $this->settings = new ilSetting('auth_saml');
    }

    public static function getInstance() : self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function isDisplayedOnLoginPage() : bool
    {
        return (bool) $this->settings->get('login_form', '0');
    }

    public function setLoginFormStatus(bool $displayed_on_login_page) : void
    {
        $this->settings->set('login_form', (string) ((int) $displayed_on_login_page));
    }
}
