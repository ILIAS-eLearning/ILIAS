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

namespace ILIAS\Calendar\ConsultationHours;

class BookingCancellationGUI
{
    public const TYPE_CANCEL = 1;
    public const TYPE_DELETE = 2;

    private \ilCalendarEntry $entry;

    private \ilBookingEntry $booking;

    private int $type;

    private $parent_object;

    private bool $has_multiple_bookings = false;

    private \ILIAS\UI\Factory $ui_factory;
    private \ILIAS\UI\Renderer $ui_renderer;
    private \ilCtrlInterface $ctrl;

    private \ilLanguage $lng;


    public function __construct(object $parent_object, \ilCalendarEntry $entry, \ilBookingEntry $booking, int $type)
    {
        global $DIC;

        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();

        $this->parent_object = $parent_object;
        $this->entry = $entry;
        $this->booking = $booking;
        $this->type = $type;

        $this->init();
    }

    public function renderModal(): \ILIAS\UI\Component\Modal\RoundTrip
    {
        $modal_title = $this->getTitle();
        $components[] = $this->getConfirmationBox();
        $components[] = $this->getInfoBox();
        if (!$this->has_multiple_bookings) {
            $components[] = $this->getBookingInfo();
        }
        return $this->ui_factory->modal()->roundtrip(
            $modal_title,
            $components,
            [
                'bookings' => $this->getInputs()
            ],
            $this->ctrl->getLinkTarget(
                $this->parent_object,
                $this->type == self::TYPE_CANCEL ? 'cancelBooking' : 'deleteBooking'
            )
        )->withCancelButtonLabel($this->lng->txt('close'))
         ->withSubmitLabel($this->getSubmitLabel());
    }

    private function getTitle(): string
    {
        switch ($this->type) {
            case self::TYPE_CANCEL:
                return $this->lng->txt('cal_cancel_booking');
            case self::TYPE_DELETE:
                return $this->lng->txt('cal_ch_delete_booking');
        }
    }

    private function getSubmitLabel(): string
    {
        switch ($this->type) {
            case self::TYPE_CANCEL:
                return $this->lng->txt('cal_cancel_booking');
            case self::TYPE_DELETE:
                return $this->lng->txt('cal_ch_delete_booking');
        }
    }

    private function getConfirmationBox(): \ILIAS\UI\Component\Component
    {
        switch ($this->type) {
            case self::TYPE_CANCEL:
                return $this->ui_factory->messageBox()->confirmation(
                    $this->lng->txt('cal_ch_cancel_booking_sure')
                );
            case self::TYPE_DELETE:
                return $this->ui_factory->messageBox()->confirmation(
                    $this->lng->txt('cal_ch_delete_booking_sure')
                );
        }
    }

    private function getInfoBox(): \ILIAS\UI\Component\Component
    {
        switch ($this->type) {
            case self::TYPE_CANCEL:
                return $this->ui_factory->messageBox()->info(
                    $this->lng->txt('cal_ch_cancel_booking_info')
                );
            case self::TYPE_DELETE:
                return $this->ui_factory->messageBox()->info(
                    $this->lng->txt('cal_ch_delete_booking_info')
                );
        }
    }

    private function getActionButton(): \ILIAS\UI\Component\Button\Button
    {
        switch ($this->type) {
            case self::TYPE_CANCEL:
                return $this->ui_factory->button()->primary(
                    $this->lng->txt('cal_cancel_booking'),
                    $this->ctrl->getLinkTarget($this->parent_object, 'cancelBooking')
                );
            case self::TYPE_DELETE:
                return $this->ui_factory->button()->primary(
                    $this->lng->txt('cal_ch_delete_booking'),
                    $this->ctrl->getLinkTarget($this->parent_object, 'deleteBooking')
                );
        }
    }

    private function getBookingInfo(): \ILIAS\UI\Component\Component
    {
        $user_info = [];
        foreach ($this->booking->getCurrentBookings($this->entry->getEntryId()) as $booking_user_id) {
            $user_info[] =
                $this->getAppointmentTitle() . ': ' .
                \ilObjUser::_lookupFullname($booking_user_id);

        }
        return $this->ui_factory->legacy()->content(implode('<br/', $user_info));
    }

    protected function getInputs()
    {
        $section_title = '';
        $inputs = [];
        if ($this->has_multiple_bookings) {
            $section_title = $this->getAppointmentTitle();
            foreach ($this->booking->getCurrentBookings($this->entry->getEntryId()) as $bookuser) {
                $inputs[(string) $bookuser] =
                    $this->ui_factory->input()->field()->checkbox(
                        \ilObjUser::_lookupFullname($bookuser)
                    )->withValue(true);
            }
        } else {
            $section_title = '';
            foreach ($this->booking->getCurrentBookings($this->entry->getEntryId()) as $bookuser) {
                $inputs[(string) $bookuser] =
                    $this->ui_factory->input()->field()->hidden(
                    )->withValue(true);
            }
        }

        return $this->ui_factory->input()->field()->section(
            $inputs,
            $section_title
        );
    }

    private function getAppointmentTitle(): string
    {
        return
            $this->entry->getTitle() . ', ' .
            \ilDatePresentation::formatDate($this->entry->getStart());
    }

    private function init(): void
    {
        $this->has_multiple_bookings = $this->booking->getCurrentNumberOfBookings($this->entry->getEntryId()) > 1;
    }
}
