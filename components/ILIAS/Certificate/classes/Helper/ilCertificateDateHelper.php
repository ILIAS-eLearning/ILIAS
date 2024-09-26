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
     * @param string|int $date
     */
    public function formatDate($date, ilObjUser $user = null, ?int $dateFormat = null): string
    {
        if (null === $dateFormat) {
            require_once __DIR__ . '/../../../Calendar/classes/class.ilDateTime.php'; // Required because of global constant IL_CAL_DATE
            $dateFormat = IL_CAL_DATETIME;
        }

        $oldDatePresentationValue = ilDatePresentation::useRelativeDates();
        ilDatePresentation::setUseRelativeDates(false);
        $date = ilDatePresentation::formatDate(
            new ilDate($date, $dateFormat),
            false,
            false,
            false,
            $user
        );
        ilDatePresentation::setUseRelativeDates($oldDatePresentationValue);

        return $date;
    }

    /**
     * @param string|int $dateTime
     * @throws ilDateTimeException
     */
    public function formatDateTime($dateTime, ilObjuser $user = null, ?int $dateFormat = null): string
    {
        if (null === $dateFormat) {
            require_once __DIR__ . '/../../../Calendar/classes/class.ilDateTime.php'; // Required because of global constant IL_CAL_DATE
            $dateFormat = IL_CAL_DATETIME;
        }

        $oldDatePresentationValue = ilDatePresentation::useRelativeDates();
        ilDatePresentation::setUseRelativeDates(false);

        $date = ilDatePresentation::formatDate(
            new ilDateTime($dateTime, $dateFormat),
            false,
            false,
            false,
            $user
        );

        ilDatePresentation::setUseRelativeDates($oldDatePresentationValue);

        return $date;
    }
}
