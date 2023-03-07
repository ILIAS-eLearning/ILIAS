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

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\DateFormat\DateFormat;

class ilIndividualAssessmentDateFormatter
{
    protected DataFactory $data_factory;

    public function __construct(DataFactory $data_factory)
    {
        $this->data_factory = $data_factory;
    }

    public function getUserDateFormat(ilObjUser $user, bool $with_time = false): DateFormat
    {
        $date_format = $this->data_factory->dateFormat()->custom();
        switch ($user->getDateFormat()) {
            case ilCalendarSettings::DATE_FORMAT_DMY:
                $date_format = $date_format->day()->dot()->month()->dot()->year();
                break;
            case ilCalendarSettings::DATE_FORMAT_MDY:
                $date_format = $date_format->month()->slash()->day()->slash()->year();
                break;
            case ilCalendarSettings::DATE_FORMAT_YMD:
            default:
                $date_format = $date_format->year()->dash()->month()->dash()->day();
        }
        if ($with_time) {
            switch ($user->getTimeFormat()) {
                case ilCalendarSettings::TIME_FORMAT_12:
                    $date_format = $date_format->space()->hours12()->colon()->minutes()->space()->meridiem();
                    break;
                case ilCalendarSettings::TIME_FORMAT_24:
                default:
                    $date_format = $date_format->space()->hours24()->colon()->minutes();
            }
        }
        return $date_format->get();
    }

    public function format(
        ilObjUser $user,
        DateTimeImmutable $datetime,
        bool $with_time = false
    ): string {
        return $this->getUserDateFormat($user, $with_time)->applyTo($datetime);
    }
}
