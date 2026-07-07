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

namespace ILIAS\BookingManager\Schedule\Table\Action;

use ilBookingSchedule;
use ilBookingScheduleGUI;
use ilCtrlInterface;
use ilGlobalTemplateInterface;
use ILIAS\BookingManager\Access\AccessManager;
use ILIAS\BookingManager\Common\HttpService;
use ILIAS\BookingManager\Common\Table\TableAction;
use ILIAS\BookingManager\Common\Table\TableActionModalTrait;
use ILIAS\BookingManager\Schedule\ScheduleManager;
use ILIAS\UI\Component\Modal\InterruptiveItem\InterruptiveItem;
use ILIAS\UI\Component\Modal\Modal;
use ILIAS\UI\Component\Table\Action\Action;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ilLanguage;

/**
 * @phpstan-type ScheduleRecord array{booking_schedule_id: int, title: string, object_has_schedule: ?bool, is_used: bool}
 * @implements TableAction<ScheduleRecord>
 */
class ScheduleTableDeleteAction implements TableAction
{
    use TableActionModalTrait;

    public const string ACTION_ID = 'delete';

    public const string ACTION_LABEL = 'delete';

    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly ilLanguage $lng,
        private readonly AccessManager $access,
        private readonly ilCtrlInterface $ctrl,
        private readonly ilGlobalTemplateInterface $tpl,
        private readonly HttpService $http,
        private readonly ScheduleManager $schedule_manager,
        private readonly int $ref_id,
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
        return $this->access->canManageSettings($this->ref_id);
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
        return $this->access->canManageSettings($this->ref_id) && !$record['is_used'];
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
                    (string) $record['booking_schedule_id'],
                    $record['title'] ?? ''
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
        if (!$this->access->canManageSettings($this->ref_id)) {
            $this->showErrorMessage($this->lng->txt('no_permission'));
            return null;
        }

        $selected_records = array_filter(
            $selected_records,
            static fn(array $record): bool => !($record['is_used'] ?? true)
        );

        foreach ($selected_records as $record) {
            (new ilBookingSchedule($record['booking_schedule_id']))->delete();
        }

        if ($selected_records !== []) {
            $this->showSuccessMessage($this->lng->txt('book_schedule_deleted'));
        }
        $this->ctrl->redirectByClass(ilBookingScheduleGUI::class, 'render');
        return null;
    }

    protected function resolveRecords(?array $selected_ids = null): array
    {
        $schedules = $this->schedule_manager->getScheduleData();

        if ($selected_ids === null) {
            return $schedules;
        }

        return array_filter(
            array_map(
                static fn(int $id): ?array => $schedules[$id] ?? null,
                $selected_ids
            )
        );
    }
}
