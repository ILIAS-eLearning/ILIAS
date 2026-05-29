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

use ilBookingObjectGUI;
use ilBookingReservation;
use ilCtrlInterface;
use ilDatePresentation;
use ilDateTime;
use ilLanguage;
use ilObjBookingPool;
use ILIAS\BookingManager\Access\AccessManager;
use ILIAS\BookingManager\Common\HttpService;
use ILIAS\BookingManager\Common\Table\TableAction;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Modal\Modal;
use ILIAS\UI\Component\Table\Action\Action;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\BookingManager\Common\Table\TableActionModalTrait;
use ilGlobalTemplateInterface;
use ILIAS\UI\Component\Modal\RoundTrip;
use ilBookingObject;
use ILIAS\BookingManager\BookingProcess\BookingProcessManager;
use ilObjUser;
use DateTimeZone;
use DateTime;

class BookableItemTableBookAction implements TableAction
{
    use TableActionModalTrait;

    public const string ACTION_ID = 'book';
    public const string ACTION_LABEL = 'book_book';

    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly ilGlobalTemplateInterface $tpl,
        private readonly ilLanguage $lng,
        private readonly HttpService $http,
        private readonly Refinery $refinery,
        private readonly ilCtrlInterface $ctrl,
        private readonly AccessManager $access,
        private readonly ilObjBookingPool $pool,
        private readonly BookingProcessManager $process_manager,
        private readonly ilObjUser $user,
        private readonly int $ref_id,
        private readonly int $booking_context_obj_id,
        private readonly array $bookable_items,
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
        $schedule_type = $this->pool->getScheduleType();

        if ($schedule_type === ilObjBookingPool::TYPE_NO_SCHEDULE) {
            return !$this->isUserBookingPoolLimitReached();
        }

        if ($schedule_type === ilObjBookingPool::TYPE_NO_SCHEDULE_PREFERENCES) {
            return false;
        }

        return $this->access->canManageOwnReservations($this->ref_id) || $this->access->canManageObjects($this->ref_id);
    }

    public function allowActionForRecord(mixed $record): bool
    {
        $schedule_type = $this->pool->getScheduleType();
        if ($schedule_type === ilObjBookingPool::TYPE_NO_SCHEDULE) {
            return !$this->isUserBookingPoolLimitReached() && !($record['has_user_active_booking'] ?? false);
        }

        return $record['is_available'] ?? false;
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
                ->withParameter($action_token, $this->getActionId())
                ->withParameter($action_type_token, self::SHOW_MODAL_ACTION),
            $row_id_token
        )->withAsync();
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
            $selected_records = array_keys($this->bookable_items);
        }

        $selected_records = $this->resolveBookingEntriesPayload($this->lng, $selected_records);
        $selected_records['entries'] = array_values(array_filter(
            $selected_records['entries'],
            fn(array $entry): bool => $this->allowActionForRecord($entry)
        ));

        if ($this->pool->getScheduleType() === ilObjBookingPool::TYPE_NO_SCHEDULE) {
            $remaining = $this->getRemainingBookingCapacity();
            if ($remaining !== null && count($selected_records['entries']) > $remaining) {
                $this->http->sendAsync(
                    $this->ui_renderer->renderAsync(
                        $this->buildBookModalInformative(
                            $this->lng->txt('book_overall_limit_would_be_exceeded')
                        )
                    )
                );
                return;
            }
        }

        $this->http->sendAsync(
            $this->ui_renderer->renderAsync(
                $this->getModal(
                    $url_builder
                        ->withParameter(
                            $row_id_token,
                            $all_records_selected
                                ? HttpService::ALL_OBJECTS
                                : array_column($selected_records['entries'], 'row_id')
                        )
                        ->withParameter($action_token, $this->getActionId())
                        ->withParameter($action_type_token, self::SUBMIT_MODAL_ACTION),
                    $selected_records,
                    $all_records_selected
                )
            )
        );
    }

    public function getModal(
        URLBuilder $url_builder,
        array $selected_records,
        bool $all_records_selected
    ): ?Modal {
        $entries = $selected_records['entries'];
        if ($entries === []) {
            return $this->buildBookModalInformative($this->lng->txt('no_valid_selection'));
        }

        $grouped = [];
        foreach ($entries as $entry) {
            $grouped[$entry['booking_object_id']][] = $entry;
        }
        ksort($grouped);

        $skipped_descriptions = $selected_records['skipped_descriptions'];
        $content = null;
        if ($skipped_descriptions !== []) {
            $content = [
                $this->ui_factory->messageBox()->confirmation(
                    $this->lng->txt('book_modal_warning_skipped_selections')
                    . $this->ui_renderer->render($this->ui_factory->listing()->unordered($skipped_descriptions))
                )
            ];
        }

        $form_components = [];
        $field_factory = $this->ui_factory->input()->field();
        foreach ($grouped as $object_id => $entries) {
            $section_input_components = [];

            foreach ($entries as $entry) {
                if (!$entry['has_schedule']) {
                    continue;
                }

                $max_quantity = $entry['max_quantity'];

                $section_input_components[$entry['row_id']] = $field_factory->numeric(
                    $this->formatBookModalSlotLabel($entry['slot_from'], $entry['slot_to']),
                    sprintf($this->lng->txt('book_objects_available'), $max_quantity)
                )
                    ->withValue(1)
                    ->withAdditionalTransformation(
                        $this->refinery->logical()->parallel(
                            [
                                $this->refinery->int()->isGreaterThanOrEqual(1),
                                $this->refinery->int()->isLessThanOrEqual($max_quantity)
                            ]
                        )
                    )
                    ->withAdditionalOnLoadCode(
                        static fn(string $id): string
                        => "
                            var element = document.getElementById('{$id}').querySelector('div input');
                            element.min = 1;
                            element.max = {$max_quantity};
                        "
                    );
            }

            if ($this->pool->usesMessages()) {
                $section_input_components['message'] = $field_factory->textarea(
                    $this->lng->txt('book_message'),
                    $this->lng->txt('book_message_info')
                );
            }

            $form_components[$object_id] = $field_factory->section(
                $section_input_components,
                $entries[0]['title'],
                $this->lng->txt('book_modal_enter_quantity_intro')
            );
        }

        return $this->ui_factory->modal()->roundtrip(
            $this->lng->txt('book_modal_booking_confirmation'),
            $content,
            $form_components,
            $url_builder->buildURI()->__toString()
        );
    }

    public function onSubmit(
        URLBuilder $url_builder,
        array $selected_records,
        bool $all_records_selected
    ): ?Modal {
        if ($this->pool->getScheduleType() === ilObjBookingPool::TYPE_NO_SCHEDULE) {
            $remaining = $this->getRemainingBookingCapacity();
            if ($remaining !== null && count($selected_records) > $remaining) {
                return $this->buildBookModalInformative(
                    $this->lng->txt('book_overall_limit_warning')
                );
            }
        }

        /** @var RoundTrip $modal */
        $modal = $this
            ->getModal(
                $url_builder,
                [
                    'entries' => $selected_records,
                    'skipped_descriptions' => []
                ],
                $all_records_selected
            );
        $modal = $modal->withRequest($this->http->getRequest());

        /** @var ?array $data */
        $data = $modal->getData();
        if ($data === null) {
            return $modal->withOnLoad($modal->getShowSignal());
        }

        $booked_total = 0;
        $unavailable = [];

        foreach ($data as $object_id => $section) {
            $message = $section['message'] ?? '';
            unset($section['message']);

            $bookable_item = new ilBookingObject($object_id);
            if ($section === [] && !$bookable_item->getScheduleId()) {
                $section = [$object_id => 1];
            }

            foreach ($section as $row_id => $amount) {
                $from_to = $bookable_item->getScheduleId() ? explode('_', $row_id) : [];

                $booked = $this->process_manager->bookAvailableObjects(
                    $bookable_item->getId(),
                    $this->user->getId(),
                    $this->user->getId(),
                    $this->booking_context_obj_id,
                    (int) ($from_to[1] ?? 0),
                    (int) ($from_to[2] ?? 0),
                    0, // TODO
                    $amount,
                    null, // TODO
                    $message
                );

                if ($booked !== []) {
                    $booked_total += count($booked);
                    continue;
                }

                $unavailable[] = $row_id;
            }
        }

        if ($unavailable !== []) {
            $this->tpl->setOnScreenMessage(
                ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE,
                $this->lng->txt('book_some_reservations_unavailable'),
                true
            );
        }

        if ($booked_total === 0 && $unavailable === []) {
            $this->tpl->setOnScreenMessage(
                ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE,
                $this->lng->txt('book_reservation_failed'),
                true
            );
        }

        if ($booked_total > 0) {
            $this->tpl->setOnScreenMessage(
                ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS,
                $this->lng->txt('book_reservation_confirmed'),
                true
            );
        }

        $this->ctrl->redirectByClass(ilBookingObjectGUI::class, 'render');
        return null;
    }

    /**
     * @param ?string[] $selected_ids
     * @return array<string, mixed>[]
     */
    protected function resolveRecords(?array $selected_ids = null): array
    {
        return $this->resolveBookingEntriesPayload(
            $this->lng,
            $selected_ids ?? array_keys($this->bookable_items)
        )['entries'];
    }

    private function buildBookModalInformative(string $message): Modal
    {
        return $this->ui_factory->modal()->roundtrip(
            $this->lng->txt('book_modal_booking_confirmation'),
            [$this->ui_factory->messageBox()->failure($message)]
        )->withAdditionalOnLoadCode(static fn(string $id): string => "il.repository.ui.initModal('$id');");
    }

    private function resolveBookingEntriesPayload(ilLanguage $lng, string|array $row_ids): array
    {
        if (!is_array($row_ids)) {
            $row_ids = $row_ids === HttpService::ALL_OBJECTS ? array_keys($this->bookable_items) : [];
        }

        $entries = [];
        $skipped_descriptions = [];

        foreach ($row_ids as $row_id) {
            $row_key = (string) $row_id;
            $record = $this->bookable_items[$row_key] ?? null;
            if ($record === null) {
                $skipped_descriptions[] = sprintf($lng->txt('book_modal_skipped_unknown_item'), $row_key);
                continue;
            }

            $availability = (int) ($record['available'] ?? 0);
            $has_schedule = ($record['schedule_id'] ?? 0) > 0;
            $slot_from = (int) ($record['slot_from'] ?? 0);
            $slot_to = (int) ($record['slot_to'] ?? 0);

            if ($availability <= 0) {
                $skipped_descriptions[] = $has_schedule
                    ? sprintf(
                        '%s — %s',
                        $record['title'],
                        $this->formatBookModalSlotLabel($slot_from, $slot_to)
                    )
                    : $record['title'];
                continue;
            }

            if (!$has_schedule && ($record['has_user_active_booking'] ?? false)) {
                $skipped_descriptions[] = $record['title'];
                continue;
            }

            $entries[] = [
                'row_id' => $row_key,
                'booking_object_id' => (int) $record['booking_object_id'],
                'title' => (string) $record['title'],
                'has_schedule' => $has_schedule,
                'has_user_booking' => (bool) ($record['has_user_booking'] ?? false),
                'slot_from' => $slot_from,
                'slot_to' => $slot_to,
                'max_quantity' => $availability,
                'is_available' => true,
            ];
        }

        return [
            'entries' => $entries,
            'skipped_descriptions' => $skipped_descriptions,
        ];
    }

    private function formatBookModalSlotLabel(int $slot_from, int $slot_to): string
    {
        $this->lng->loadLanguageModule('dateplaner');

        return ilDatePresentation::formatPeriod(
            new ilDateTime($slot_from, IL_CAL_UNIX),
            new ilDateTime($slot_to, IL_CAL_UNIX)
        );
    }

    private function getRemainingBookingCapacity(): ?int
    {
        $limit = $this->pool->getOverallLimit();
        if ($limit === null || $limit === 0) {
            return null;
        }

        $current = ilBookingReservation::isBookingPoolLimitReachedByUser(
            $this->user->getId(),
            $this->pool->getId()
        );

        return max(0, $limit - $current);
    }

    private function isUserBookingPoolLimitReached(): bool
    {
        return $this->getRemainingBookingCapacity() === 0;
    }
}
