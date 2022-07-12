<?php declare(strict_types=1);

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
 * Repository for LSGlobalSettings over ILIAS global settings
 */
class ilLSGlobalSettingsDB implements LSGlobalSettingsDB
{
    const SETTING_POLL_INTERVAL = 'lso_polling_interval';
    const POLL_INTERVAL_DEFAULT = 10; //in seconds

    protected ilSetting $il_settings;

    public function __construct(\ilSetting $il_settings)
    {
        $this->il_settings = $il_settings;
    }

    public function getSettings() : LSGlobalSettings
    {
        $interval_seconds = (float) $this->il_settings->get(
            self::SETTING_POLL_INTERVAL,
            (string) self::POLL_INTERVAL_DEFAULT
        );

        return new LSGlobalSettings($interval_seconds);
    }

    public function storeSettings(LSGlobalSettings $settings) : void
    {
        $this->il_settings->set(
            self::SETTING_POLL_INTERVAL,
            (string) $settings->getPollingIntervalSeconds()
        );
    }
}
