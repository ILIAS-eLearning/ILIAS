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
use ilGlobalTemplateInterface;
use ILIAS\BookingManager\Access\AccessManager;
use ILIAS\BookingManager\Common\HttpService;
use ILIAS\BookingManager\Common\Table\TableAction;
use ILIAS\BookingManager\Common\Table\TableActionModalTrait;
use ILIAS\UI\Component\Modal\InterruptiveItem\InterruptiveItem;
use ILIAS\UI\Component\Modal\Modal;
use ILIAS\UI\Component\Table\Action\Action;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ilLanguage;
use ilObjBookingPool;
use ILIAS\UI\Renderer as UIRenderer;

class BookableItemTableDeleteAction implements TableAction
{
    use TableActionModalTrait;

    public const string ACTION_ID = 'delete';
    public const string ACTION_LABEL = 'delete_bookable_item';

    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly ilLanguage $lng,
        private readonly ilGlobalTemplateInterface $tpl,
        private readonly HttpService $http,
        private readonly AccessManager $access,
        private readonly ilObjBookingPool $pool,
        private readonly int $ref_id,
        private readonly bool $active_management,
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
        return $this->active_management && $this->access->canManageObjects($this->ref_id);
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
        return true;
    }

    public function getModal(
        URLBuilder $url_builder,
        array $selected_records,
        bool $all_records_selected
    ): ?Modal {
        return $this->ui_factory->modal()->interruptive(
            $this->lng->txt('confirm'),
            $this->lng->txt('book_confirm_delete'),
            $url_builder->buildURI()->__toString()
        )->withAffectedItems(
            array_map(
                fn(array $record): InterruptiveItem => $this->ui_factory->modal()->interruptiveItem()->standard(
                    (string) $record['booking_object_id'],
                    (string) $record['title']
                ),
                $selected_records
            )
        )->withActionButtonLabel($this->lng->txt('delete'));
    }

    public function onSubmit(
        URLBuilder $url_builder,
        array $selected_records,
        bool $all_records_selected
    ): ?Modal {
        if (!$this->access->canManageObjects($this->ref_id)) {
            $this->showErrorMessage($this->lng->txt('no_permission'));
            return null;
        }

        foreach ($selected_records as $record) {
            $object_id = (int) ($record['booking_object_id'] ?? 0);
            if ($object_id <= 0) {
                continue;
            }

            $object = new ilBookingObject($object_id);
            $object->deleteReservationsAndCalEntries($object_id);
            $object->delete();
        }

        $this->showSuccessMessage($this->lng->txt('book_object_deleted'));
        return null;
    }

    /**
     * @param ?string[] $selected_ids
     * @return array<string, mixed>[]
     */
    protected function resolveRecords(?array $selected_ids = null): array
    {
        $by_id = [];
        foreach (ilBookingObject::getList($this->pool->getId()) as $item) {
            $by_id[(int) $item['booking_object_id']] = [
                'booking_object_id' => (int) $item['booking_object_id'],
                'title' => (string) $item['title'],
            ];
        }

        if ($selected_ids === null) {
            return array_values($by_id);
        }

        $unique = array_values(array_unique(array_map('intval', $selected_ids)));
        $result = [];
        foreach ($unique as $object_id) {
            if (!isset($by_id[$object_id])) {
                continue;
            }

            $result[] = $by_id[$object_id];
        }

        return $result;
    }
}
