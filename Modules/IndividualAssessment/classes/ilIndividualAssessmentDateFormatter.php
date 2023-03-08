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
        $df = $this->data_factory->dateFormat();
        switch ($user->getDateFormat()) {
            case ilCalendarSettings::DATE_FORMAT_DMY:
                $date_format = $df->germanShort();
                break;
            case ilCalendarSettings::DATE_FORMAT_MDY:
                //americanShort
                $date_format = $df->custom()->month()->slash()->day()->slash()->year()->get();
                break;
            case ilCalendarSettings::DATE_FORMAT_YMD:
            default:
                $date_format = $df->standard();
        }
        if ($with_time) {
            switch ($user->getTimeFormat()) {
                case ilCalendarSettings::TIME_FORMAT_12:
                    $date_format = $df->withTime12($date_format);
                    break;
                case ilCalendarSettings::TIME_FORMAT_24:
                default:
                    $date_format = $df->withTime24($date_format);
            }
        }
        return $date_format;
    }

    public function format(
        ilObjUser $user,
        DateTimeImmutable $datetime,
        bool $with_time = false
    ): string {
        return $this->getUserDateFormat($user, $with_time)->applyTo($datetime);
    }
}
