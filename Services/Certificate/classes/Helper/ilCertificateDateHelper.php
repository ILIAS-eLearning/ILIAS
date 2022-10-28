<?php

declare(strict_types=1);

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
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateDateHelper
{
    /**
     * @param string|int $date
     * @param ?int       $dateFormat
     * @return string
     */
    public function formatDate($date, ?int $dateFormat = null): string
    {
        if (null === $dateFormat) {
            require_once 'Services/Calendar/classes/class.ilDateTime.php'; // Required because of global contant IL_CAL_DATE
            $dateFormat = IL_CAL_DATETIME;
        }

        $oldDatePresentationValue = ilDatePresentation::useRelativeDates();
        ilDatePresentation::setUseRelativeDates(false);

        $date = ilDatePresentation::formatDate(new ilDate($date, $dateFormat));

        ilDatePresentation::setUseRelativeDates($oldDatePresentationValue);

        return $date;
    }

    /**
     * @param string|int $dateTime
     * @param ?int       $dateFormat
     * @return string
     * @throws ilDateTimeException
     */
    public function formatDateTime($dateTime, ?int $dateFormat = null): string
    {
        if (null === $dateFormat) {
            require_once 'Services/Calendar/classes/class.ilDateTime.php'; // Required because of global contant IL_CAL_DATE
            $dateFormat = IL_CAL_DATETIME;
        }

        $oldDatePresentationValue = ilDatePresentation::useRelativeDates();
        ilDatePresentation::setUseRelativeDates(false);

        $date = ilDatePresentation::formatDate(new ilDateTime($dateTime, $dateFormat));

        ilDatePresentation::setUseRelativeDates($oldDatePresentationValue);

        return $date;
    }
}
