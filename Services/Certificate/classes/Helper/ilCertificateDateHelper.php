<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateDateHelper
{
    /**
     * @param string|int $date
     * @param ?int $dateFormat
     * @return string
     */
    public function formatDate($date, ?int $dateFormat = null) : string
    {
        if (null === $dateFormat) {
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
     * @param ?int $dateFormat
     * @return string
     * @throws ilDateTimeException
     */
    public function formatDateTime($dateTime, ?int $dateFormat = null) : string
    {
        if (null === $dateFormat) {
            $dateFormat = IL_CAL_DATETIME;
        }

        $oldDatePresentationValue = ilDatePresentation::useRelativeDates();
        ilDatePresentation::setUseRelativeDates(false);

        $date = ilDatePresentation::formatDate(new ilDateTime($dateTime, $dateFormat));

        ilDatePresentation::setUseRelativeDates($oldDatePresentationValue);

        return $date;
    }
}
