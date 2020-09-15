<?php declare(strict_types=1);

/**
 * Repository for LSGlobalSettings over ILIAS global settings
 */
class ilLSGlobalSettingsDB implements LSGlobalSettingsDB
{
    const SETTING_POLL_INTERVAL = 'lso_polling_interval';
    const POLL_INTERVAL_DEFAULT = 10; //in seconds

    /**
     * @var IlSettings
     */
    protected $il_settings;

    public function __construct(\ilSetting $il_settings)
    {
        $this->il_settings = $il_settings;
    }

    public function getSettings() : LSGlobalSettings
    {
        $interval_seconds = (float) $this->il_settings->get(
            self::SETTING_POLL_INTERVAL,
            self::POLL_INTERVAL_DEFAULT
        );

        return new LSGlobalSettings($interval_seconds);
    }

    public function storeSettings(LSGlobalSettings $settings)
    {
        $this->il_settings->set(
            self::SETTING_POLL_INTERVAL,
            $settings->getPollingIntervalSeconds()
        );
    }
}
