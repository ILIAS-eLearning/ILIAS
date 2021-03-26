<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Cron for booking pools
 * - Automatic booking for overdue pools with booking by preferences
 *
 * @author <killing@leifos.com>
 */
class ilBookingPrefBookCron extends ilCronJob
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
    }

    public function getId()
    {
        return "book_pref_book";
    }

    public function getTitle()
    {
        $lng = $this->lng;

        $lng->loadLanguageModule("book");

        return $lng->txt("book_pref_book_cron");
    }

    public function getDescription()
    {
        $lng = $this->lng;

        $lng->loadLanguageModule("book");

        return $lng->txt("book_pref_book_cron_info");
    }

    public function getDefaultScheduleType()
    {
        return self::SCHEDULE_TYPE_DAILY;
    }

    public function getDefaultScheduleValue()
    {
        return;
    }

    public function hasAutoActivation()
    {
        return true;
    }

    public function hasFlexibleSchedule()
    {
        return true;
    }

    public function run()
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
