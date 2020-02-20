<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * User settings configuration (what preferences can be visible/changed/...)
 *
 * @author killing@leifos.de
 */
class ilUserSettingsConfig
{
    const HIDE_PREFIX = "usr_settings_hide_";
    const DISABLED_PREFIX = "usr_settings_disable_";

    /**
     * Constructor
     */
    public function __construct(ilSetting $settings = null)
    {
        global $DIC;

        $this->settings = (is_null($settings))
            ? $DIC->settings()
            : $settings;
        $this->setting = $this->settings->getAll();
    }

    /**
     * Is field visible and changeable by user?
     * @param $field
     * @return bool
     */
    public function isVisibleAndChangeable($field) : bool
    {
        return $this->isVisible($field) && $this->isChangeable($field);
    }

    /**
     * Is setting visible to user?
     * @param string $field
     * @return bool
     */
    public function isVisible(string $field) : bool
    {
        return (!(isset($this->setting[self::HIDE_PREFIX . $field]) &&
            $this->setting[self::HIDE_PREFIX . $field] == 1));
    }

    /**
     * Is setting changeable by user?
     * @param string $field
     * @return bool
     */
    public function isChangeable(string $field) : bool
    {
        return (!(isset($this->setting[self::DISABLED_PREFIX . $field]) &&
            $this->setting[self::DISABLED_PREFIX . $field] == 1));
    }

    /**
     * Set a profile field being visible
     * @param string $field
     * @param bool $visible
     */
    public function setVisible(string $field, bool $visible)
    {
        if (!$visible) {
            $this->settings->set(self::HIDE_PREFIX . $field, "1");
            $this->setting[self::HIDE_PREFIX . $field] = 1;
        } else {
            $this->settings->delete(self::HIDE_PREFIX . $field);
            unset($this->setting[self::HIDE_PREFIX . $field]);
        }
    }

    /**
     * Set a profile field being changeable
     * @param string $field
     * @param bool $changeable
     */
    public function setChangeable(string $field, bool $changeable)
    {
        if (!$changeable) {
            $this->settings->set(self::DISABLED_PREFIX . $field, "1");
            $this->setting[self::DISABLED_PREFIX . $field] = 1;
        } else {
            $this->settings->delete(self::DISABLED_PREFIX . $field);
            unset($this->setting[self::DISABLED_PREFIX . $field]);
        }
    }
}
