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

use ILIAS\Cron\Job\Schedule\JobScheduleType;
use ILIAS\Cron\Job\JobResult;
use ILIAS\Cron\CronJob;

/**
 * Cron for booking manager notification
 * @author Alexander Killing <killing@leifos.de>
 */
class ilBookCronNotification extends CronJob
{
    protected \ILIAS\BookingManager\InternalRepoService $repo;
    protected ilLanguage $lng;
    protected ilLogger $book_log;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();

        $this->book_log = ilLoggerFactory::getLogger("book");
        $this->repo = $DIC->bookingManager()
            ->internal()
            ->repo();
    }

    public function getId(): string
    {
        return "book_notification";
    }

    public function getTitle(): string
    {
        $lng = $this->lng;

        $lng->loadLanguageModule("book");
        return $lng->txt("book_notification");
    }

    public function getDescription(): string
    {
        $lng = $this->lng;

        $lng->loadLanguageModule("book");
        return $lng->txt("book_notification_info");
    }

    public function getDefaultScheduleType(): JobScheduleType
    {
        return JobScheduleType::DAILY;
    }

    public function getDefaultScheduleValue(): ?int
    {
        return null;
    }

    public function hasAutoActivation(): bool
    {
        return false;
    }

    public function hasFlexibleSchedule(): bool
    {
        return false;
    }

    public function run(): JobResult
    {
        $status = JobResult::STATUS_NO_ACTION;

        $count = $this->sendNotifications();

        if ($count > 0) {
            $status = JobResult::STATUS_OK;
        }

        $result = new JobResult();
        $result->setStatus($status);

        return $result;
    }

    protected function sendNotifications(): int
    {
        global $DIC;

        $access = $DIC->bookingManager()->internal()->domain()->access();

        $log = $this->book_log;

        $log->debug("start");


        $notifications = [];

        /*
         * pool id 123 > 2 days, ...
         */
        foreach (ilObjBookingPool::getPoolsWithReminders() as $p) {
            // determine reservations from max(next day $last_to_ts) up to "rmd_day" days + 1
            // per pool id
            $next_day_ts = mktime(0, 0, 0, date('n'), (int) date('j') + 1);
            $log->debug("next day ts: " . $next_day_ts);
            $last_reminder_to_ts = $p["last_remind_ts"];
            // for debug purposes
            // $last_reminder_to_ts-= 24*60*60;
            $log->debug("last_reminder ts: " . $last_reminder_to_ts);
            $from_ts = max($next_day_ts, $last_reminder_to_ts);
            $log->debug("from ts: " . $from_ts);
            $to_ts = mktime(0, 0, 0, date('n'), (int) date('j') + $p["reminder_day"] + 1);
            $res = [];

            // overwrite from to current time, see #26216, this ensures
            // that all reservations are sent, some multiple times (each day)
            // we include all reservations from now to the period set in the pool settings
            $from_ts = time();

            // additional logging info, see #26216
            $log->debug("pool id: "
                . $p["booking_pool_id"]
                . "(" . ilObject::_lookupTitle($p["booking_pool_id"]) . ") "
                . ", "
                . date("Y-m-d, H:i:s", $from_ts)
                . " to " . date("Y-m-d, H:i:s", $to_ts));


            if ($to_ts > $from_ts) {
                $repo = $this->repo->reservation();
                $res = $repo->getListByDate(true, null, [
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
                    if ($access->canRetrieveNotificationsForOwnReservationsByObjId(
                        (int) $p["booking_pool_id"],
                        (int) $r["user_id"]
                    )) {
                        $log->debug("got read");
                        $notifications[$r["user_id"]]["personal"][$r["pool_id"]][] = $r;
                    }
                }

                // admins
                foreach ($user_ids as $uid) {
                    $log->debug("check write for user id: " . $uid . ", pool: " . $p["booking_pool_id"]);

                    if ($access->canRetrieveNotificationsForAllReservationsByObjId(
                        (int) $p["booking_pool_id"],
                        (int) $r["user_id"]
                    )) {
                        $log->debug("got write");
                        $notifications[$uid]["admin"][$r["pool_id"]][] = $r;
                    }
                }
            }
            ilObjBookingPool::writeLastReminderTimestamp($p["booking_pool_id"], $to_ts);
        }

        // send mails
        $this->sendMails($notifications);

        return count($notifications);
    }

    protected function sendMails(
        array $notifications
    ): void {
        foreach ($notifications as $uid => $n) {
            $ntf = new ilSystemNotification();
            $lng = $ntf->getUserLanguage($uid);
            $lng->loadLanguageModule("book");

            $txt = "";
            if (is_array($n["personal"] ?? null)) {
                $txt .= "\n" . $lng->txt("book_your_reservations") . "\n";
                $txt .= "-----------------------------------------\n";
                foreach ($n["personal"] as $obj_id => $reservs) {
                    $txt .= ilObject::_lookupTitle($obj_id) . ":\n";
                    foreach ($reservs as $r) {
                        $txt .= "- " . $r["title"] . " (" . $r["counter"] . "), " .
                            ilDatePresentation::formatDate(new ilDate($r["date"], IL_CAL_DATE)) . ", " .
                            $r["slot"] . "\n";
                    }
                }
            }

            if (is_array($n["admin"] ?? null)) {
                $txt .= "\n" . $lng->txt("book_reservation_overview") . "\n";
                $txt .= "-----------------------------------------\n";
                foreach ($n["admin"] as $obj_id => $reservs) {
                    $txt .= ilObject::_lookupTitle($obj_id) . ":\n";
                    foreach ($reservs as $r) {
                        $txt .= "- " . $r["title"] . " (" . $r["counter"] . "), " . $r["user_name"] . ", " .
                            ilDatePresentation::formatDate(new ilDate($r["date"], IL_CAL_DATE)) . ", " .
                            $r["slot"] . "\n";
                        if ($r["message"] != "") {
                            $txt .= "  " . $lng->txt("book_message") .
                                ": " . $r["message"];
                        }
                    }
                }
            }
            $ntf->setLangModules(array("book"));
            $ntf->setSubjectLangId("book_booking_reminders");
            $ntf->setIntroductionLangId("book_rem_intro");
            $ntf->addAdditionalInfo("", $txt);
            $ntf->setReasonLangId("book_rem_reason");
            $this->book_log->debug("send Mail: " . $uid);
            $ntf->sendMailAndReturnRecipients([$uid]);
        }
    }


}
