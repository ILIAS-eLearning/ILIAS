<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

class ilDashboardSidePanelSettingsRepository
{
    public const CALENDAR = 'cal';
    public const NEWS = 'news';
    public const MAIL = 'mail';
    public const TASKS = 'task';

    protected ilSetting $setting;

    public function __construct(ilSetting $dashboard_settings = null)
    {
        $this->setting = is_null($dashboard_settings)
            ? new ilSetting('dash')
            : $dashboard_settings;
    }

    /**
     * @return string[]
     */
    public function getValidModules(): array
    {
        return [
            self::TASKS,
            self::CALENDAR,
            self::NEWS,
            self::MAIL,
        ];
    }

    /**
     * @param string[] $positions
     */
    public function setPositions(array $positions): void
    {
        $this->setting->set('side_panel_positions', serialize($positions));
    }

    /**
     * @return string[]
     */
    public function getPositions(): array
    {
        $positions = $this->setting->get('side_panel_positions', '');
        $modules = [];
        if ($positions !== '') {
            $modules = unserialize($positions, ['allowed_classes' => false]);
        }
        $all_modules = $this->getValidModules();
        foreach ($all_modules as $mod) {
            if (!in_array($mod, $modules, true)) {
                $modules[] = $mod;
            }
        }
        return $modules;
    }

    protected function isValidModule(string $mod): bool
    {
        return in_array($mod, $this->getValidModules());
    }

    public function enable(string $mod, bool $active): void
    {
        if ($this->isValidModule($mod)) {
            $this->setting->set('enable_' . $mod, $active ? '1' : '0');
        }
    }

    public function isEnabled(string $mod): bool
    {
        if ($this->isValidModule($mod)) {
            return (bool) $this->setting->get('enable_' . $mod, '1');
        }
        return false;
    }
}
