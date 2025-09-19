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
    public function __construct()
    {
        class_exists('ilDateTime'); // Ensure all global constants are defined
    }

    /**
     * @param string|int $raw_date_input
     * @throws ilDateTimeException
     * @throws InvalidArgumentException
     */
    public function formatDate($raw_date_input, ?ilObjUser $user = null, ?int $date_format = null): string
    {
        return $this->format(
            $raw_date_input,
            $user,
            $date_format ?? $this->autoDetectFormat($raw_date_input, IL_CAL_DATE),
            false
        );
    }

    /**
     * @param string|int $raw_datetime_input
     * @throws ilDateTimeException
     * @throws InvalidArgumentException
     */
    public function formatDateTime($raw_datetime_input, ?ilObjUser $user = null, ?int $datetime_format = null): string
    {
        return $this->format(
            $raw_datetime_input,
            $user,
            $datetime_format ?? $this->autoDetectFormat($raw_datetime_input, IL_CAL_DATETIME),
            true
        );
    }

    /**
     * @param int|string $raw
     * @throws ilDateTimeException
     * @throws InvalidArgumentException
     */
    private function format($raw, ?ilObjUser $user, int $format, bool $has_time): string
    {
        $this->assertFormatMatchesInput($raw, $format);

        if ($format === IL_CAL_UNIX) {
            $raw = (int) $raw;
        } else {
            $raw = (string) $raw;
        }

        $restore_rel = ilDatePresentation::useRelativeDates();
        ilDatePresentation::setUseRelativeDates(false);
        try {
            $dateObj = $has_time
                ? new ilDateTime($raw, $format)
                : new ilDate($raw, $format);

            return ilDatePresentation::formatDate(
                $dateObj,
                false,
                false,
                false,
                $user
            );
        } finally {
            ilDatePresentation::setUseRelativeDates($restore_rel);
        }
    }

    /**
     * @param string|int $value
     */
    private function autoDetectFormat($value, int $default_when_not_unix): int
    {
        return $this->isProbablyUnixTimestamp($value) ? IL_CAL_UNIX : $default_when_not_unix;
    }

    /**
     * @param string|int $value
     * @throws InvalidArgumentException
     */
    private function assertFormatMatchesInput($value, int $format): void
    {
        $is_unix_like = $this->isProbablyUnixTimestamp($value);

        if ($format === IL_CAL_UNIX && !$is_unix_like) {
            throw new InvalidArgumentException('Non-numeric input given for IL_CAL_UNIX');
        }

        if ($format !== IL_CAL_UNIX && $is_unix_like) {
            throw new InvalidArgumentException('Unix timestamp given but format is not IL_CAL_UNIX');
        }
    }

    /**
     * @param int|string $maybe_timestamp
     */
    private function isProbablyUnixTimestamp($maybe_timestamp): bool
    {
        if (is_int($maybe_timestamp)) {
            return true;
        }

        if (!is_string($maybe_timestamp) || !ctype_digit($maybe_timestamp)) {
            return false;
        }

        try {
            $datetime = DateTimeImmutable::createFromFormat('Ymd', $maybe_timestamp);
            return $datetime === false;
        } catch (Throwable) {
            // Fallthrough
        }

        return true;
    }
}
