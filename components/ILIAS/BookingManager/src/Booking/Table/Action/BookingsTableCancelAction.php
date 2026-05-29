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

namespace ILIAS\BookingManager\Bookings\Table\Action;

use ilBookingObject;
use ilBookingReservation;
use ilBookingReservationsGUI;
use ilCalendarEntry;
use ilCtrlInterface;
use ilDatePresentation;
use ilDateTime;
use ilGlobalTemplateInterface;
use ILIAS\BookingManager\Access\AccessManager;
use ILIAS\BookingManager\Common\HttpService;
use ILIAS\BookingManager\Common\Table\TableAction;
use ILIAS\BookingManager\Common\Table\TableActionModalTrait;
use ILIAS\BookingManager\Reservations\ReservationDBRepository;
use ILIAS\Language\Language;
use ILIAS\UI\Component\Modal\InterruptiveItem\Standard;
use ILIAS\UI\Component\Modal\Modal;
use ILIAS\UI\Component\Table\Action\Action;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ilObjBookingPool;
use ilUserUtil;

class BookingsTableCancelAction implements TableAction
{
    use TableActionModalTrait;

    public const string ACTION_ID = 'cancel';

    public const string ACTION_LABEL = 'book_set_cancel';

    public function __construct(
        private readonly AccessManager $access,
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly Language $lng,
        private readonly ReservationDBRepository $reservation_repository,
        private readonly HttpService $http,
        private readonly ilGlobalTemplateInterface $tpl,
        private readonly ilCtrlInterface $ctrl,
        private readonly ilObjBookingPool $booking_pool,
        private readonly array $bookings
    ) {
    }

    public function getActionId(): string
    {
        return self::ACTION_ID;
    }

    public function getActionLabel(): string
    {
        return self::ACTION_LABEL;
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function allowActionForRecord(mixed $record): bool
    {
        if (!$this->access->canManageReservationForUser($this->booking_pool->getRefId(), $record['user_id'])) {
            return false;
        }

        return
            in_array($record['status'], [ilBookingReservation::STATUS_IN_USE, 0])
            && ($this->booking_pool->getScheduleType() !== ilObjBookingPool::TYPE_FIX_SCHEDULE || $record['date_from'] > time());
    }

    public function getTableAction(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token
    ): Action {
        return $this->ui_factory->table()->action()->standard(
            $this->lng->txt($this->getActionLabel()),
            $url_builder
                ->withParameter($action_token, self::ACTION_ID)
                ->withParameter($action_type_token, self::SHOW_MODAL_ACTION),
            $row_id_token
        )->withAsync();
    }

    protected function getModal(
        URLBuilder $url_builder,
        array $selected_records,
        bool $all_records_selected
    ): ?Modal {
        return $this->ui_factory->modal()->interruptive(
            $this->lng->txt('confirm'),
            $this->lng->txt('book_confirm_cancel'),
            $url_builder->buildURI()->__toString()
        )->withAffectedItems(
            array_map(
                fn(array $selected_record): Standard => $this->ui_factory->modal()->interruptiveItem()->standard(
                    (string) $selected_record['booking_reservation_id'],
                    $this->formatBookingDescription($selected_record)
                ),
                $selected_records
            )
        )->withActionButtonLabel($this->lng->txt($this->getActionLabel()));
    }

    protected function onSubmit(URLBuilder $url_builder, array $selected_records, bool $all_records_selected): ?Modal
    {
        $allowed = true;
        foreach ($selected_records as $selected_record) {
            if (!$this->access->canManageReservationForUser($this->booking_pool->getRefId(), $selected_record['user_id'])) {
                $allowed = false;
                break;
            }
        }

        if (!$allowed) {
            $this->showErrorMessage($this->lng->txt('no_permission'));
            $this->ctrl->redirectByClass(ilBookingReservationsGUI::class, ilBookingReservationsGUI::DEFAULT_CMD);
            return null;
        }

        foreach ($selected_records as $selected_record) {
            $booking_reservation = new ilBookingReservation($selected_record['booking_reservation_id']);
            $booking_reservation->setStatus(ilBookingReservation::STATUS_CANCELLED);
            $booking_reservation->update();

            if ($this->booking_pool->getScheduleType() !== ilObjBookingPool::TYPE_NO_SCHEDULE) {
                $calendar_entry_id = $booking_reservation->getCalendarEntry();
                if ($calendar_entry_id) {
                    (new ilCalendarEntry($calendar_entry_id))->delete();
                }
            }
        }

        $this->showSuccessMessage($this->lng->txt('book_reservation_cancelled'));
        $this->ctrl->redirectByClass(ilBookingReservationsGUI::class, ilBookingReservationsGUI::DEFAULT_CMD);
        return null;
    }

    protected function resolveRecords(?array $selected_ids = null): array
    {
        return array_map(
            fn(int $selected_record): array => $this->bookings[$selected_record],
            $selected_ids ?? array_keys($this->bookings)
        );
    }

    protected function formatBookingDescription(array $booking): string
    {
        $title = (new ilBookingObject($booking['object_id']))->getTitle();
        $user = strip_tags(ilUserUtil::getNamePresentation($booking['user_id']));
        $parts = [$title, $user];
        if ($this->booking_pool->getScheduleType() !== ilObjBookingPool::TYPE_NO_SCHEDULE && $booking['date_from']) {
            $parts[] = ilDatePresentation::formatPeriod(
                new ilDateTime($booking['date_from'], IL_CAL_UNIX),
                new ilDateTime($booking['date_to'] + 1, IL_CAL_UNIX)
            );
        }
        return implode(' – ', $parts);
    }
}
