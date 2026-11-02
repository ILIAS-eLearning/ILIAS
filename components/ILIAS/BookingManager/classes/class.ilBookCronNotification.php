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

use ILIAS\BookingManager\Access\AccessManager;
use ILIAS\BookingManager\Notification\AdminReservation;
use ILIAS\BookingManager\Notification\PersonalReservation;
use ILIAS\BookingManager\Notification\PoolReservations;
use ILIAS\BookingManager\Notification\ReminderMailText;
use ILIAS\BookingManager\Notification\ReminderSection;
use ILIAS\BookingManager\Notification\Reservation;
use ILIAS\Cron\Job\Schedule\JobScheduleType;
use ILIAS\Cron\Job\JobResult;
use ILIAS\Cron\CronJob;
use ILIAS\Data\ReferenceId;
use ILIAS\StaticURL\Builder\URIBuilder;

/**
 * Cron for booking manager notification
 * @author Alexander Killing <killing@leifos.de>
 */
class ilBookCronNotification extends CronJob
{
    protected \ILIAS\BookingManager\InternalRepoService $repo;
    protected ilLanguage $lng;
    protected ilLogger $book_log;
    protected ilAccessHandler $access;
    protected AccessManager $booking_access;
    protected URIBuilder $uri_builder;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->book_log = ilLoggerFactory::getLogger("book");
        $this->repo = $DIC->bookingManager()
            ->internal()
            ->repo();
        $this->access = $DIC->access();
        $this->booking_access = $DIC->bookingManager()->internal()->domain()->access();
        $this->uri_builder = $DIC['static_url']->builder();
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
                    if ($this->booking_access->canRetrieveNotificationsForOwnReservationsByObjId(
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

                    if ($this->booking_access->canRetrieveNotificationsForAllReservationsByObjId(
                        (int) $p["booking_pool_id"],
                        (int) $uid
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

            $txt = $this->buildReminderBody($lng, $n, (int) $uid);
            $ntf->setLangModules(array("book"));
            $ntf->setSubjectLangId("book_booking_reminders");
            $ntf->setIntroductionLangId("book_rem_intro");
            $ntf->addAdditionalInfo("", $txt);
            $ntf->setReasonLangId("book_rem_reason");
            $this->book_log->debug("send Mail: " . $uid);
            $ntf->sendMailAndReturnRecipients([$uid]);
        }
    }

    protected function buildReminderBody(ilLanguage $language, array $notification, int $user_id): string
    {
        return (string) new ReminderMailText(
            $this->personalSection($language, $notification['personal'] ?? null, $user_id),
            $this->adminSection($language, $notification['admin'] ?? null, $user_id)
        );
    }

    private function personalSection(ilLanguage $language, mixed $pools, int $user_id): ?ReminderSection
    {
        if (!is_array($pools)) {
            return null;
        }

        $blocks = [];
        foreach ($pools as $pool_id => $reservations) {
            $lines = [];
            foreach ($reservations as $reservation) {
                $lines[] = new PersonalReservation(
                    (string) $reservation['title'],
                    (int) $reservation['counter'],
                    ilDatePresentation::formatDate(new ilDate((string) $reservation['date'], IL_CAL_DATE))
                        . ', ' . $reservation['slot']
                );
            }
            $blocks[] = $this->poolBlock((int) $pool_id, $user_id, $lines);
        }

        return new ReminderSection($language->txt('book_your_reservations'), $blocks);
    }

    private function adminSection(ilLanguage $language, mixed $pools, int $user_id): ?ReminderSection
    {
        if (!is_array($pools)) {
            return null;
        }

        $blocks = [];
        foreach ($pools as $pool_id => $reservations) {
            $lines = [];
            foreach ($reservations as $reservation) {
                $lines[] = new AdminReservation(
                    (string) $reservation['title'],
                    (int) $reservation['counter'],
                    (string) $reservation['user_name'],
                    ilDatePresentation::formatDate(new ilDate((string) $reservation['date'], IL_CAL_DATE))
                        . ', ' . $reservation['slot'],
                    $reservation['message'] != ''
                        ? $language->txt('book_message') . ': ' . $reservation['message']
                        : ''
                );
            }
            $blocks[] = $this->poolBlock((int) $pool_id, $user_id, $lines);
        }

        return new ReminderSection($language->txt('book_reservation_overview'), $blocks);
    }

    /**
     * @param list<Reservation> $reservations
     */
    private function poolBlock(int $pool_id, int $user_id, array $reservations): PoolReservations
    {
        return new PoolReservations(
            ilObject::_lookupTitle($pool_id),
            $this->poolGotoLink($pool_id, $user_id),
            $reservations
        );
    }

    private function poolGotoLink(int $pool_obj_id, int $user_id): string
    {
        foreach (ilObject::_getAllReferences($pool_obj_id) as $ref_id) {
            if ($this->access->checkAccessOfUser($user_id, 'read', '', $ref_id)) {
                return (string) $this->uri_builder->build('book', new ReferenceId((int) $ref_id));
            }
        }

        return '';
    }
}
