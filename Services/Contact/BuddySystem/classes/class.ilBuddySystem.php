<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBuddySystem
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystem
{
    /** @var self */
    protected static $instance;

    /** @var bool */
    protected static $isEnabled;

    /** @var ilSetting */
    protected $settings;

    /** @var ilObjUser */
    protected $user;

    /**
     * ilBuddySystem constructor.
     */
    protected function __construct()
    {
        global $DIC;

        $this->settings = new ilSetting('buddysystem');
        $this->user = $DIC['ilUser'];
    }

    /**
     * @return self
     */
    public static function getInstance() : self
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param string $keyword
     * @param mixed $value
     */
    public function setSetting(string $keyword, $value) : void
    {
        $this->settings->set($keyword, $value);
    }

    /**
     * @param string $keyword
     * @param bool|false $default
     * @return string
     */
    public function getSetting(string $keyword, bool $default = false)
    {
        return $this->settings->get($keyword, $default);
    }

    /**
     * @return bool
     */
    public function isEnabled() : bool
    {
        if (self::$isEnabled !== null) {
            return self::$isEnabled;
        }

        if ($this->user->isAnonymous()) {
            self::$isEnabled = false;
            return false;
        }

        self::$isEnabled = (bool) $this->settings->get('enabled', false);
        return self::$isEnabled;
    }
}