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

class ilCertificateDateHelper
{
    /**
     * @param string|int $raw_date_input
     */
    public function formatDate($raw_date_input, ?ilObjUser $user = null, ?int $date_format = null): string
    {
        require_once __DIR__ . '/../../../Calendar/classes/class.ilDateTime.php'; // Required because of global constant IL_CAL_DATE

        if ($date_format === null) {
            $date_format = IL_CAL_DATETIME;
        }

        if ($date_format === IL_CAL_UNIX) {
            $raw_date_input = (int) $raw_date_input;
        } else {
            $raw_date_input = (string) $raw_date_input;
        }

        $oldDatePresentationValue = ilDatePresentation::useRelativeDates();
        ilDatePresentation::setUseRelativeDates(false);
        $raw_date_input = ilDatePresentation::formatDate(
            new ilDate($raw_date_input, $date_format),
            false,
            false,
            false,
            $user
        );
        ilDatePresentation::setUseRelativeDates($oldDatePresentationValue);

        return $raw_date_input;
    }

    /**
     * @param string|int $raw_datetime_input
     * @throws ilDateTimeException
     */
    public function formatDateTime($raw_datetime_input, ?ilObjuser $user = null, ?int $datetime_format = null): string
    {
        require_once __DIR__ . '/../../../Calendar/classes/class.ilDateTime.php'; // Required because of global constant IL_CAL_DATE

        if ($datetime_format === null) {
            $datetime_format = IL_CAL_DATETIME;
        }

        if ($datetime_format === IL_CAL_UNIX) {
            $raw_datetime_input = (int) $raw_datetime_input;
        } else {
            $raw_datetime_input = (string) $raw_datetime_input;
        }

        $oldDatePresentationValue = ilDatePresentation::useRelativeDates();
        ilDatePresentation::setUseRelativeDates(false);

        $date = ilDatePresentation::formatDate(
            new ilDateTime($raw_datetime_input, $datetime_format),
            false,
            false,
            false,
            $user
        );

        ilDatePresentation::setUseRelativeDates($oldDatePresentationValue);

        return $date;
    }
}
