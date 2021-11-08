<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBuddySystem
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystem
{
    protected static ?self $instance = null;
    protected static ?bool $isEnabled = null;

    protected ilSetting $settings;
    protected ilObjUser $user;

    protected function __construct()
    {
        global $DIC;

        $this->settings = new ilSetting('buddysystem');
        $this->user = $DIC['ilUser'];
    }

    public static function getInstance() : self
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param string $keyword
     * @param string $value
     */
    public function setSetting(string $keyword, string $value) : void
    {
        $this->settings->set($keyword, $value);
    }

    /**
     * @param string $keyword
     * @param string|null $default
     * @return string|null
     */
    public function getSetting(string $keyword, ?string $default = null) : ?string
    {
        return $this->settings->get($keyword, $default);
    }

    public function isEnabled() : bool
    {
        if (self::$isEnabled !== null) {
            return self::$isEnabled;
        }

        if ($this->user->isAnonymous()) {
            self::$isEnabled = false;
            return false;
        }

        self::$isEnabled = (bool) $this->settings->get('enabled', '0');
        return self::$isEnabled;
    }
}
