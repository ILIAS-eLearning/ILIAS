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

namespace ILIAS\BookingManager\BookableItem\Table\Action;

use ilBookingObject;
use ilBookingReservation;
use ilDatePresentation;
use ilDateTime;
use ilGlobalTemplateInterface;
use ILIAS\BookingManager\Access\AccessManager;
use ILIAS\BookingManager\Common\HttpService;
use ILIAS\BookingManager\Common\Table\TableAction;
use ILIAS\BookingManager\Common\Table\TableActionModalTrait;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Modal\InterruptiveItem\InterruptiveItem;
use ILIAS\UI\Component\Modal\Modal;
use ILIAS\UI\Component\Table\Action\Action;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ilLanguage;
use ilObjBookingPool;
use ilObjUser;

class BookableItemTableCancelBookingAction implements TableAction
{
    use TableActionModalTrait;

    public const string ACTION_ID = 'cancel_booking';
    public const string ACTION_LABEL = 'book_set_cancel';
    public const string SHOW_MODAL_ACTION = 'showModalAction';
    public const string SUBMIT_MODAL_ACTION = 'submitModalAction';

    private readonly array $bookings;

    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly ilLanguage $lng,
        private readonly ilGlobalTemplateInterface $tpl,
        private readonly HttpService $http,
        private readonly Refinery $refinery,
        private readonly AccessManager $access,
        private readonly ilObjBookingPool $pool,
        private readonly ilObjUser $user,
        private readonly int $ref_id,
        private readonly bool $active_management,
        private readonly array $bookable_items,
    ) {
        $filter = [];
        if (
            !$this->access->canManageAllReservations($this->pool->getRefId())
            && !$this->access->canReadPublicLog($this->pool->getRefId())
        ) {
            $filter['user_id'] = $this->user->getId();
        }
        $this->bookings = array_column(
            ilBookingReservation::getList(array_keys($this->bookable_items), 1000, 0, $filter)['data'],
            null,
            'booking_reservation_id'
        );
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
        if ($this->pool->getScheduleType() === ilObjBookingPool::TYPE_NO_SCHEDULE_PREFERENCES) {
            return false;
        }

        return
            $this->active_management
            && ($this->access->canManageOwnReservations($this->ref_id) || $this->access->canManageObjects($this->ref_id));
    }

    public function getTableAction(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token
    ): Action {
        return $this->ui_factory->table()->action()->standard(
            $this->lng->txt(self::ACTION_LABEL),
            $url_builder
                ->withParameter($action_token, self::ACTION_ID)
                ->withParameter($action_type_token, self::SHOW_MODAL_ACTION),
            $row_id_token
        )->withAsync();
    }

    public function allowActionForRecord(mixed $record): bool
    {
        return $record['has_user_active_booking'] ?? false;
    }

    public function onExecute(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token
    ): ?Modal {
        return match ($this->http->resolveRowParameter($action_type_token->getName())) {
            self::SUBMIT_MODAL_ACTION => $this->submit($url_builder, $row_id_token, $action_token, $action_type_token),
            default => $this->showModal(
                $url_builder,
                $row_id_token,
                $action_token,
                $action_type_token
            ),
        };
    }

    protected function getModal(
        URLBuilder $url_builder,
        array $selected_records,
        bool $all_records_selected
    ): ?Modal {
        return $this->ui_factory->modal()->interruptive(
            $this->lng->txt('book_confirm_cancel'),
            $this->lng->txt('book_confirm_cancel_info'),
            $url_builder->buildURI()->__toString()
        )->withAffectedItems(array_map(
            fn(array $record): InterruptiveItem => $this->ui_factory->modal()->interruptiveItem()->standard(
                (string) $record['reservation_id'],
                $this->buildItemDescription($record)
            ),
            $this->resolveRecords($selected_records)
        ))->withActionButtonLabel($this->lng->txt('book_set_cancel'));
    }

    protected function showModal(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token
    ): void {
        $selected_records = $this->http->resolveRowParameters($row_id_token->getName());
        $all_records_selected = $selected_records === HttpService::ALL_OBJECTS;
        if ($all_records_selected) {
            $selected_records = array_map(
                fn(array $record): string => "{$record['object_id']}_{$record['date_from']}_{$record['date_to']}",
                $this->bookings
            );
        }

        $this->http->sendAsync(
            $this->ui_renderer->renderAsync(
                $this->getModal(
                    $url_builder
                        ->withParameter(
                            $row_id_token,
                            $all_records_selected
                                ? HttpService::ALL_OBJECTS
                                : $selected_records
                        )
                        ->withParameter($action_token, self::ACTION_ID)
                        ->withParameter($action_type_token, self::SUBMIT_MODAL_ACTION),
                    $selected_records,
                    $all_records_selected
                )
            )
        );
    }

    protected function onSubmit(
        URLBuilder $url_builder,
        array $selected_records,
        bool $all_records_selected
    ): ?Modal {
        if (!$this->access->canManageOwnReservations($this->ref_id)) {
            $this->tpl->setOnScreenMessage(
                ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE,
                $this->lng->txt('no_permission'),
                true
            );
            return null;
        }

        $cancelled = 0;
        foreach ($selected_records as $record) {
            $reservation = new ilBookingReservation((int) $record['reservation_id']);
            $reservation->setStatus(ilBookingReservation::STATUS_CANCELLED);
            $reservation->update();
            $cancelled++;
        }

        if ($cancelled === 0) {
            $this->tpl->setOnScreenMessage(
                ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE,
                $this->lng->txt('no_valid_selection'),
                true
            );
            return null;
        }

        $this->tpl->setOnScreenMessage(
            ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS,
            $this->lng->txt('book_reservation_cancelled'),
            true
        );
        return null;
    }

    /**
     * @return string[]
     */
    private function resolveRowIds(string $key): array
    {
        $value = $this->http->get(
            $key,
            $this->refinery->custom()->transformation(
                static function (mixed $raw): array {
                    if ($raw === null || $raw === '') {
                        return [];
                    }

                    if (is_array($raw)) {
                        return array_values(array_map('strval', $raw));
                    }

                    return [(string) $raw];
                }
            )
        ) ?? [];

        return array_values(array_filter($value, static fn(string $v): bool => $v !== ''));
    }

    /**
     * @param string[] $selected_ids
     * @return array[]
     */
    protected function resolveRecords(?array $selected_ids = null): array
    {
        $titles = [];
        foreach (ilBookingObject::getList($this->pool->getId()) as $item) {
            $titles[(int) $item['booking_object_id']] = (string) $item['title'];
        }

        if ($selected_ids === null) {
            $selected_ids = array_map(
                fn(array $record): string => "{$record['object_id']}_{$record['date_from']}_{$record['date_to']}",
                $this->bookings
            );
        }

        $result = [];
        $user_id = $this->user->getId();
        foreach ($selected_ids as $row_id) {
            $row_id = (string) $row_id;
            $parts = explode('_', $row_id);
            $object_id = (int) $parts[0];
            if ($object_id <= 0 || !isset($titles[$object_id])) {
                continue;
            }

            $reservations = ilBookingReservation::getList([$object_id], 1000, 0, []);
            foreach ($reservations['data'] ?? [] as $reservation) {
                if ((int) $reservation['user_id'] !== $user_id) {
                    continue;
                }

                if ((int) $reservation['status'] === ilBookingReservation::STATUS_CANCELLED) {
                    continue;
                }

                $slot_from = (int) $reservation['date_from'];
                $slot_to = (int) $reservation['date_to'];
                if (count($parts) === 3) {
                    if ($slot_from !== (int) $parts[1] || $slot_to !== (int) $parts[2]) {
                        continue;
                    }
                }

                $result[] = [
                    'reservation_id' => (int) ($reservation['booking_reservation_id'] ?? $reservation['id'] ?? 0),
                    'object_id' => $object_id,
                    'title' => $titles[$object_id],
                    'slot_from' => $slot_from,
                    'slot_to' => $slot_to + 1,
                    'has_schedule' => count($parts) === 3,
                    'has_user_active_booking' =>
                        $user_id === (int) $reservation['user_id']
                        && (int) $reservation['status'] !== ilBookingReservation::STATUS_CANCELLED,
                ];

                if (count($parts) === 3) {
                    break;
                }
            }
        }

        return $result;
    }

    private function buildItemDescription(array $record): string
    {
        if (!$record['has_schedule']) {
            return $record['title'];
        }

        $this->lng->loadLanguageModule('dateplaner');
        $period = ilDatePresentation::formatPeriod(
            new ilDateTime($record['slot_from'], IL_CAL_UNIX),
            new ilDateTime($record['slot_to'] - 1, IL_CAL_UNIX)
        );

        return "{$record['title']} ({$period})";
    }
}
