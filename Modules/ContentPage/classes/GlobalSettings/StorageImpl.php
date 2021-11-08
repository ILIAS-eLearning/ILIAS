<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\ContentPage\GlobalSettings;

use ilSetting;

/**
 * Class StorageImpl
 * @package ILIAS\ContentPage\GlobalSettings
 * @author Michael Jansen <mjansen@databay.de>
 */
class StorageImpl implements Storage
{
    private const P_READING_TIME_STATUS = 'reading_time_status';

    private ilSetting $globalSettings;

    public function __construct(ilSetting $globalSettings)
    {
        $this->globalSettings = $globalSettings;
    }

    public function getSettings() : Settings
    {
        $settings = new Settings();

        if ($this->globalSettings->get(self::P_READING_TIME_STATUS, '0')) {
            $settings = $settings->withEnabledReadingTime();
        } else {
            $settings = $settings->withDisabledReadingTime();
        }

        return $settings;
    }

    public function store(Settings $settings) : void
    {
        $this->globalSettings->set(self::P_READING_TIME_STATUS, ((string) (int) $settings->isReadingTimeEnabled()));
    }
}
