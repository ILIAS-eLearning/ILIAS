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

namespace ILIAS\Calendar\ConsultationHours;

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ilConsultationHourAppointments;
use ilDatePresentation;
use ilBookingEntry;
use ilUtil;
use ilObjUser;
use ilObject;
use ilLink;
use DateTimeImmutable;
use ilArrayUtil;
use ILIAS\StaticURL;
use ILIAS\Data\ReferenceId;
use ilConsultationHoursGUI;
use ilCalendarEntry;
use ilDate;
use ilDateTime;

class BookingDataProvider
{
    private int $user_id;

    private array $data = [];
    private StaticURL\Services $static_url_service;
    private \ILIAS\UI\Factory $ui_factory;

    private string $vm_period = ilConsultationHoursGUI::VIEW_MODE_PERIOD_ALL;
    private string $vm_status = ilConsultationHoursGUI::VIEW_MODE_STATUS_ALL;

    public function __construct(int $user_id, string $vm_period, string $vm_status)
    {
        global $DIC;

        $this->user_id = $user_id;
        $this->vm_status = $vm_status;
        $this->vm_period = $vm_period;
        $this->static_url_service = $DIC['static_url'];
        $this->ui_factory = $DIC->ui()->factory();
        $this->read();
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    protected function read(): void
    {
        $data = [];
        $counter = 0;
        foreach (ilConsultationHourAppointments::getAppointments($this->getUserId()) as $appointment) {
            $booking = new ilBookingEntry($appointment->getContextId());
            $booked_user_ids = $booking->getCurrentBookings($appointment->getEntryId());
            if ($this->isFiltered($appointment, $booking, $booked_user_ids)) {
                continue;
            }

            $row = [];
            $row['id'] = (string) $appointment->getEntryId();
            $row['booking_start'] = (new \DateTimeImmutable())->setTimestamp($appointment->getStart()->getUnixTime());
            $row['booking_duration'] =
                (int) ($appointment->getEnd()->getUnixTime() - $appointment->getStart()->getUnixTime()) / 60;
            $row['booking_title'] = $appointment->getTitle();

            $obj_ids = array_map(
                'intval',
                ilUtil::_sortIds($booking->getTargetObjIds(), 'object_data', 'title', 'obj_id')
            );
            // locations
            $locations = [];
            foreach ($obj_ids as $obj_id) {
                $link = null;
                foreach (ilObject::_getAllReferences($obj_id) as $ref_id) {
                    $link = $this->static_url_service->builder()->build(
                        ilObject::_lookupType($obj_id),
                        new ReferenceId($ref_id)
                    );
                    $locations[] = $this->ui_factory->link()->standard(
                        ilObject::_lookupTitle($obj_id),
                        (string) $link
                    );
                    break;
                }
            }
            $row['booking_location'] = $this->ui_factory->listing()->unordered($locations);

            // booking users
            $booked_user_ids = array_map('intval', ilUtil::_sortIds($booked_user_ids, 'usr_data', 'lastname', 'usr_id'));
            $bookings = [];
            $comments = [];
            foreach ($booked_user_ids as $booked_user_id) {
                $fullname = ilObjUser::_lookupFullname($booked_user_id);
                $link = \ilUserUtil::getProfileLink($booked_user_id);
                $ui_link = $this->ui_factory->link()->standard(
                    $fullname,
                    $link ? $link : '#'
                );
                if ($link === '') {
                    $ui_link = $ui_link->withDisabled(true);
                }
                $bookings[] = $ui_link;

                $comment = ilBookingEntry::lookupBookingMessage(
                    $appointment->getEntryId(),
                    $booked_user_id
                );
                if (trim($comment) !== '') {
                    $ui_link = $this->ui_factory->link()
                        ->standard(
                            $fullname . ': "' . $comment . '"',
                            '#'
                        )
                        ->withDisabled(true);
                    $comments[] = $ui_link;
                }

            }
            $row['booking_participant'] = $this->ui_factory->listing()->unordered($bookings);
            $row['booking_comment'] = $this->ui_factory->listing()->unordered($comments);
            $data[$counter++] = $row;
        }
        $this->data = $data;
    }

    public function isFiltered(ilCalendarEntry $entry, ilBookingEntry $booking, array $booked_users): bool
    {
        if (
            $this->vm_status == ilConsultationHoursGUI::VIEW_MODE_STATUS_ALL &&
            $this->vm_period == ilConsultationHoursGUI::VIEW_MODE_PERIOD_ALL
        ) {
            return false;
        }
        $now = new ilDate(time(), IL_CAL_UNIX);
        if ($this->vm_period === ilConsultationHoursGUI::VIEW_MODE_PERIOD_UPCOMING) {
            if (ilDateTime::_before($entry->getStart(), $now, IL_CAL_DAY)) {
                return true;
            }
        }
        if ($this->vm_period === ilConsultationHoursGUI::VIEW_MODE_PERIOD_PAST) {
            if (ilDateTime::_after($entry->getStart(), $now, IL_CAL_DAY)) {
                return true;
            }
        }
        if ($this->vm_status === ilConsultationHoursGUI::VIEW_MODE_STATUS_OPEN) {
            if (count($booked_users) >= $booking->getNumberOfBookings()) {
                return true;
            }
        }
        if ($this->vm_status === ilConsultationHoursGUI::VIEW_MODE_STATUS_BOOKED) {
            if (count($booked_users) < $booking->getNumberOfBookings()) {
                return true;
            }
        }
        return false;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function sortData(Order $order): array
    {
        $data = $this->getData();
        [$order_field, $order_direction] = $order->join([], fn($ret, $key, $value) => [$key, $value]);
        usort($data, fn($a, $b) => $a[$order_field] <=> $b[$order_field]);
        if ($order_direction === 'DESC') {
            $data = array_reverse($data);
        }
        return $data;
    }

    public function limitData(Range $range, Order $order): array
    {
        return array_slice($this->sortData($order), $range->getStart(), $range->getLength());
    }



}
