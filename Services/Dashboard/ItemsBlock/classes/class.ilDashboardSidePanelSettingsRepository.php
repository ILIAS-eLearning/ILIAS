<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Dashboard side panel settings Repo
 *
 * @author killing@leifos.de
 */
class ilDashboardSidePanelSettingsRepository
{
    public const CALENDAR = "cal";
    public const NEWS = "news";
    public const MAIL = "mail";
    public const TASKS = "task";

    /**
     * @var ilSetting
     */
    protected $setting;

    /**
     * Constructor
     */
    public function __construct(ilSetting $dashboard_settings = null)
    {
        $this->setting = is_null($dashboard_settings)
            ? new ilSetting("dash")
            : $dashboard_settings;
    }

    /**
     * Get valid modules
     *
     * @return array
     */
    public function getValidModules() : array
    {
        return [
            self::CALENDAR,
            self::NEWS,
            self::MAIL,
            self::TASKS
        ];
    }

    /**
     *
     * @param string $mod
     * @return bool
     */
    protected function isValidModule(string $mod) : bool
    {
        return in_array($mod, $this->getValidModules());
    }


    /**
     * Enable
     *
     * @param string $mod
     * @param bool $active
     */
    public function enable(string $mod, bool $active) : void
    {
        if ($this->isValidModule($mod)) {
            $this->setting->set("enable_" . $mod, (int) $active);
        }
    }

    /**
     * Is enabled
     *
     * @param string $mod
     * @return bool
     */
    public function isEnabled(string $mod) : bool
    {
        if ($this->isValidModule($mod)) {
            return (bool) $this->setting->get("enable_" . $mod, true);
        }
        return false;
    }
}
