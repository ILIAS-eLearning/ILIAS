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

/**
 * User settings configuration (what preferences can be visible/changed/...)
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUserSettingsConfig
{
    public const HIDE_PREFIX = "usr_settings_hide_";
    public const DISABLED_PREFIX = "usr_settings_disable_";
    /**
     * @var array<string,string>
     */
    protected array $setting;
    protected ?ilSetting $settings;

    public function __construct(
        ilSetting $settings = null
    ) {
        global $DIC;

        $this->settings = (is_null($settings))
            ? $DIC->settings()
            : $settings;
        $this->setting = $this->settings->getAll();
    }

    /**
     * Is field visible and changeable by user?
     */
    public function isVisibleAndChangeable(
        string $field
    ): bool {
        return $this->isVisible($field) && $this->isChangeable($field);
    }

    /**
     * Is setting visible to user?
     */
    public function isVisible(
        string $field
    ): bool {
        return (!(isset($this->setting[self::HIDE_PREFIX . $field]) &&
            $this->setting[self::HIDE_PREFIX . $field] == 1));
    }

    /**
     * Is setting changeable by user?
     */
    public function isChangeable(
        string $field
    ): bool {
        return (!(isset($this->setting[self::DISABLED_PREFIX . $field]) &&
            $this->setting[self::DISABLED_PREFIX . $field] == 1));
    }

    /**
     * Set a profile field being visible
     */
    public function setVisible(string $field, bool $visible): void
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
     */
    public function setChangeable(string $field, bool $changeable): void
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
