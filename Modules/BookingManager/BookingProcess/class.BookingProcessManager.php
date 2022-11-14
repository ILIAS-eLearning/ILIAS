<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *********************************************************************/

namespace ILIAS\BookingManager\BookingProcess;

use ILIAS\BookingManager\InternalDataService;
use ILIAS\BookingManager\InternalRepoService;
use ILIAS\BookingManager\InternalDomainService;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class BookingProcessManager
{
    protected InternalDataService $data;
    protected InternalRepoService $repo;
    protected InternalDomainService $domain;

    public function __construct(
        InternalDataService $data,
        InternalRepoService $repo,
        InternalDomainService $domain
    ) {
        $this->data = $data;
        $this->repo = $repo;
        $this->domain = $domain;
    }

    /**
     * Get missing availability of an object for
     * a given weekly recurrence, object id, starting slot and requested nr
     */
    public function getRecurrenceMissingAvailability(
        int $obj_id,
        int $slot_from,
        int $slot_to,
        int $recurrence_weeks,
        int $requested_nr,
        \ilDate $until
    ) : array {
        $end = $until->get(IL_CAL_UNIX);
        $cut = 0;
        $cycle = $recurrence_weeks * 7;
        $booked_out_slots = [];
        $check_slot_from = $slot_from;
        while ($cut < 1000 && $check_slot_from <= $end) {
            $check_slot_from = $this->addDaysStamp($slot_from, $cycle * $cut);
            $check_slot_to = $this->addDaysStamp($slot_to, $cycle * $cut);
            $available = \ilBookingReservation::getAvailableObject(array($obj_id), $check_slot_from, $check_slot_to, false, true);
            $available = $available[$obj_id];
            if ($available < $requested_nr) {
                $booked_out_slots[] = [
                    "from" => $check_slot_from,
                    "to" => $check_slot_to,
                    "missing" => $requested_nr - $available
                ];
            }
            $cut++;
        }
        return $booked_out_slots;
    }

    protected function addDaysDate(
        string $a_date,
        int $a_days
    ) : string {
        $date = date_parse($a_date);
        $stamp = mktime(0, 0, 1, $date["month"], $date["day"] + $a_days, $date["year"]);
        return date("Y-m-d", $stamp);
    }

    protected function addDaysStamp(
        int $a_stamp,
        int $a_days
    ) : int {
        $date = getdate($a_stamp);
        return mktime(
            $date["hours"],
            $date["minutes"],
            $date["seconds"],
            $date["mon"],
            $date["mday"] + $a_days,
            $date["year"]
        );
    }

    public function bookAvailableObjects(
        int $obj_id,
        int $user_to_book,
        int $assigner_id,
        int $context_obj_id,
        int $from,
        int $to,
        int $recurrence,
        int $nr,
        ?\ilDateTime $until
    ) : array {
        $reservation_repo = $this->repo->reservation();

        $rsv_ids = [];

        if (is_null($until)) {
            $end = $from;
            $cut = 999;     // only one iteration
            $cycle = 0;     // no recurrence
        } else {
            $end = $until->get(IL_CAL_UNIX);
            $cut = 0;
            $cycle = $recurrence * 7;
        }
        $booked_out_slots = [];
        $check_slot_from = $from;
        $group_id = $reservation_repo->getNewGroupId();
        while ($cut < 1000 && $check_slot_from <= $end) {
            $check_slot_from = $this->addDaysStamp($from, $cycle * $cut);
            $check_slot_to = $this->addDaysStamp($to, $cycle * $cut);
            $available = \ilBookingReservation::getAvailableObject(array($obj_id), $check_slot_from, $check_slot_to, false, true);
            $available = $available[$obj_id];
            $book_nr = min($nr, $available);
            for ($loop = 0; $loop < $book_nr; $loop++) {
                $rsv_ids[] = $this->bookSingle(
                    $obj_id,
                    $user_to_book,
                    $assigner_id,
                    $context_obj_id,
                    $check_slot_from,
                    $check_slot_to,
                    $group_id
                );
                $success = $obj_id;
            }
            $cut++;
        }
        return $rsv_ids;
    }

    /**
     * Book object for date
     * @return int reservation id
     * @throws \ilDateTimeException
     */
    public function bookSingle(
        int $object_id,
        int $user_to_book,
        int $assigner_id = 0,
        int $context_obj_id = 0,
        int $a_from = null,
        int $a_to = null,
        int $a_group_id = null
    ) : int {
        $lng = $this->domain->lng();

        // add user to participants, if not existing
        $pool_id = \ilBookingObject::lookupPoolId($object_id);
        $this->domain->participants()->createIfNotExisting($user_to_book, $pool_id);

        // create new reservation
        $reservation = new \ilBookingReservation();
        $reservation->setObjectId($object_id);
        $reservation->setUserId($user_to_book);
        $reservation->setAssignerId($assigner_id);
        $reservation->setFrom((int) $a_from);
        $reservation->setTo((int) $a_to);
        $reservation->setGroupId((int) $a_group_id);
        $reservation->setContextObjId($context_obj_id);
        $reservation->save();

        // create calendar entry
        if ($a_from) {
            $lng->loadLanguageModule('dateplaner');
            $def_cat = \ilCalendarUtil::initDefaultCalendarByType(
                \ilCalendarCategory::TYPE_BOOK,
                $user_to_book,
                $lng->txt('cal_ch_personal_book'),
                true
            );

            $object = new \ilBookingObject($object_id);

            $entry = new \ilCalendarEntry();
            $entry->setStart(new \ilDateTime($a_from, IL_CAL_UNIX));
            $entry->setEnd(new \ilDateTime($a_to, IL_CAL_UNIX));
            $entry->setTitle($lng->txt('book_cal_entry') . ' ' . $object->getTitle());
            $entry->setContextId($reservation->getId());
            $entry->save();

            $assignment = new \ilCalendarCategoryAssignments($entry->getEntryId());
            if ($def_cat !== null) {
                $assignment->addAssignment($def_cat->getCategoryID());
            }
        }

        return $reservation->getId();
    }
}
