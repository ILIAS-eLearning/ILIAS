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

/**
 * Cron for booking pools
 * - Automatic booking for overdue pools with booking by preferences
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilBookingPrefBookCron extends ilCronJob
{
    protected ilLanguage $lng;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
    }

    public function getId(): string
    {
        return "book_pref_book";
    }

    public function getTitle(): string
    {
        $lng = $this->lng;

        $lng->loadLanguageModule("book");

        return $lng->txt("book_pref_book_cron");
    }

    public function getDescription(): string
    {
        $lng = $this->lng;

        $lng->loadLanguageModule("book");

        return $lng->txt("book_pref_book_cron_info");
    }

    public function getDefaultScheduleType(): int
    {
        return self::SCHEDULE_TYPE_DAILY;
    }

    public function getDefaultScheduleValue(): ?int
    {
        return null;
    }

    public function hasAutoActivation(): bool
    {
        return true;
    }

    public function hasFlexibleSchedule(): bool
    {
        return true;
    }

    public function run(): ilCronJobResult
    {
        $cron_status = ilCronJobResult::STATUS_NO_ACTION;
        $message = "";

        $auto_book = new ilBookingPrefAutoBooking();
        $auto_book->run();

        $cron_status = ilCronJobResult::STATUS_OK;

        $cron_result = new ilCronJobResult();
        $cron_result->setStatus($cron_status);

        return $cron_result;
    }
}
