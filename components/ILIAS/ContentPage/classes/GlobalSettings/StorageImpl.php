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

    public function __construct(private readonly ilSetting $globalSettings)
    {
    }

    public function getSettings(): Settings
    {
        $settings = new Settings();

        if ($this->globalSettings->get(self::P_READING_TIME_STATUS, '0')) {
            $settings = $settings->withEnabledReadingTime();
        } else {
            $settings = $settings->withDisabledReadingTime();
        }

        return $settings;
    }

    public function store(Settings $settings): void
    {
        $this->globalSettings->set(self::P_READING_TIME_STATUS, ((string) (int) $settings->isReadingTimeEnabled()));
    }
}
