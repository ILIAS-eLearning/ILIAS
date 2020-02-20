<?php declare(strict_types=1);
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilSamlSettings
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilSamlSettings
{
    /** @var self */
    protected static $instance = null;
    /** @var ilSetting */
    protected $settings;

    /**
     * ilSamlSettings constructor.
     */
    protected function __construct()
    {
        $this->settings = new ilSetting('auth_saml');
    }

    /**
     * @return self
     */
    public static function getInstance() : self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @return bool
     */
    public function isDisplayedOnLoginPage() : bool
    {
        return (bool) $this->settings->get('login_form', 0);
    }

    /**
     * @param bool $displayed_on_login_page
     */
    public function setLoginFormStatus(bool $displayed_on_login_page) : void
    {
        $this->settings->set('login_form', (int) $displayed_on_login_page);
    }
}
