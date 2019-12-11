<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Cron/classes/class.ilCronJob.php";

/**
 * Cron for booking manager notification
 *
 * @author Alex Killing <killing@leifos.com>
 * @ingroup ModulesBookingManager
 */
class ilBookCronNotification extends ilCronJob
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilLogger
     */
    protected $book_log;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        if (isset($DIC["ilAccess"])) {
            $this->access = $DIC->access();
        }

        $this->book_log = ilLoggerFactory::getLogger("book");
    }

    public function getId()
    {
        return "book_notification";
    }
    
    public function getTitle()
    {
        $lng = $this->lng;
        
        $lng->loadLanguageModule("book");
        return $lng->txt("book_notification");
    }
    
    public function getDescription()
    {
        $lng = $this->lng;
        
        $lng->loadLanguageModule("book");
        return $lng->txt("book_notification_info");
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
        return false;
    }
    
    public function hasFlexibleSchedule()
    {
        return false;
    }
    
    public function run()
    {
        $status = ilCronJobResult::STATUS_NO_ACTION;

        $count = $this->sendNotifications();

        if ($count > 0) {
            $status = ilCronJobResult::STATUS_OK;
        }
        
        $result = new ilCronJobResult();
        $result->setStatus($status);
        
        return $result;
    }

    /**
     * Send notifications
     *
     * @param
     * @return int
     */
    protected function sendNotifications()
    {
        $log = $this->book_log;

        // get all booking pools with notification setting



        $log->debug("start");


        $notifications = [];

        /*
         * pool id 123 > 2 days, ...
         */
        foreach (ilObjBookingPool::getPoolsWithReminders() as $p) {
            // determine reservations from max(next day $last_to_ts) up to "rmd_day" days + 1
            // per pool id
            $next_day_ts = mktime(0, 0, 0, date('n'), date('j') + 1);
            $log->debug("next day ts: " . $next_day_ts);
            $last_reminder_to_ts = $p["last_remind_ts"];
            // for debug purposes
            // $last_reminder_to_ts-= 24*60*60;
            $log->debug("last_reminder ts: " . $last_reminder_to_ts);
            $from_ts = max($next_day_ts, $last_reminder_to_ts);
            $log->debug("from ts: " . $from_ts);
            $to_ts = mktime(0, 0, 0, date('n'), date('j') + $p["reminder_day"] + 1);
            $res = [];

            $log->debug("pool id: " . $p["booking_pool_id"] . ", " . $from_ts . "-" . $to_ts);

            if ($to_ts > $from_ts) {
                $res = ilBookingReservation::getListByDate(true, null, [
                    "from" => $from_ts,
                    "to" => $to_ts
                ], [$p["booking_pool_id"]]);
            }

            $log->debug("reservations: " . count($res));

            //var_dump($res); exit;

            // get subscriber of pool id
            $user_ids = ilNotification::getNotificationsForObject(ilNotification::TYPE_BOOK, $p["booking_pool_id"]);

            $log->debug("users: " . count($user_ids));

            // group by user, type, pool
            foreach ($res as $r) {

                // users
                $log->debug("check notification of user id: " . $r["user_id"]);
                if (in_array($r["user_id"], $user_ids)) {
                    if ($this->checkAccess("read", $r["user_id"], $p["booking_pool_id"])) {
                        $notifications[$r["user_id"]]["personal"][$r["pool_id"]][] = $r;
                    }
                }

                // admins
                foreach ($user_ids as $uid) {
                    $log->debug("check write for user id: " . $uid . ", pool: " . $p["booking_pool_id"]);

                    if ($this->checkAccess("write", $uid, $p["booking_pool_id"])) {
                        $log->debug("got write");
                        $notifications[$uid]["admin"][$r["pool_id"]][] = $r;
                    }
                }
            }
            ilObjBookingPool::writeLastReminderTimestamp($p["booking_pool_id"], $to_ts);
        }

        $log->debug("notifications to users: " . count($notifications));

        // send mails
        $this->sendMails($notifications);

        return count($notifications);
    }

    /**
     * Send mails
     *
     * @param
     */
    protected function sendMails($notifications)
    {
        foreach ($notifications as $uid => $n) {
            include_once "./Services/Notification/classes/class.ilSystemNotification.php";
            $ntf = new ilSystemNotification();
            $lng = $ntf->getUserLanguage($uid);
            $lng->loadLanguageModule("book");

            $txt = "";
            if (is_array($n["personal"])) {
                $txt.= "\n" . $lng->txt("book_your_reservations") . "\n";
                $txt.= "-----------------------------------------\n";
                foreach ($n["personal"] as $obj_id => $reservs) {
                    $txt.= ilObject::_lookupTitle($obj_id) . ":\n";
                    foreach ($reservs as $r) {
                        $txt.= "- " . $r["title"] . " (" . $r["counter"] . "), " .
                            ilDatePresentation::formatDate(new ilDate($r["date"], IL_CAL_DATE)) . ", " .
                            $r["slot"] . "\n";
                    }
                }
            }

            if (is_array($n["admin"])) {
                $txt.= "\n" . $lng->txt("book_reservation_overview") . "\n";
                $txt.= "-----------------------------------------\n";
                foreach ($n["admin"] as $obj_id => $reservs) {
                    $txt.= ilObject::_lookupTitle($obj_id) . ":\n";
                    foreach ($reservs as $r) {
                        $txt.= "- " . $r["title"] . " (" . $r["counter"] . "), " . $r["user_name"] . ", " .
                            ilDatePresentation::formatDate(new ilDate($r["date"], IL_CAL_DATE)) . ", " .
                            $r["slot"] . "\n";
                    }
                }
            }

            $ntf->setLangModules(array("book"));
            $ntf->setSubjectLangId("book_booking_reminders");
            $ntf->setIntroductionLangId("book_rem_intro");
            $ntf->addAdditionalInfo("", $txt);
            $ntf->setReasonLangId("book_rem_reason");
            $ntf->sendMail(array($uid));
        }
    }


    /**
     * check access on obj id
     *
     * @param
     * @return
     */
    protected function checkAccess($perm, $uid, $obj_id)
    {
        $access = $this->access;
        foreach (ilObject::_getAllReferences($obj_id) as $ref_id) {
            if ($access->checkAccessOfUser($uid, $perm, "", $ref_id)) {
                return true;
            }
        }
        return false;
    }


    /**
     * Send user notifications
     *
     * @return int
     */
    protected function sendUserNotifications($res)
    {
        /*
         * Your reservations for tomorrow
         *
         * Pool Title
         * Pool Link
         * - Object (cnt), From - To
         * - ...
         *
         * Reservations for tomorrow
         *
         * Pool Title
         * Pool Link
         * - Object (cnt), From - To
         * - ...
         *
         */
    }

    /**
     * Send admin notifications
     *
     * @return int
     */
    protected function sendAdminNotifications($res)
    {
    }
}
