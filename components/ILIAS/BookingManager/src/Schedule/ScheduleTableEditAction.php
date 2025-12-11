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

namespace ILIAS\BookingManager\Schedule;

use ilCtrlInterface;
use ILIAS\BookingManager\Access\AccessManager;
use ILIAS\BookingManager\Common\Table\TableAction;
use ILIAS\BookingManager\HttpService;
use ILIAS\UI\Component\Table\Action\Action;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ilLanguage;

/**
 * @phpstan-type ScheduleRecord array{booking_schedule_id: int, title: string, object_has_schedule: ?bool, is_used: bool}
 * @implements TableAction<ScheduleRecord>
 */
class ScheduleTableEditAction implements TableAction
{
    public const string ACTION_ID = 'edit';

    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly ilLanguage $lng,
        private readonly AccessManager $access,
        private readonly ilCtrlInterface $ctrl,
        private readonly HttpService $http_service,
        private readonly int $ref_id
    ) {
    }

    public function getActionId(): string
    {
        return self::ACTION_ID;
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
        return $this->ui_factory->table()->action()->single(
            $this->lng->txt('edit'),
            $url_builder
                ->withParameter($action_token, self::ACTION_ID)
                ->withParameter($action_type_token, 'edit'), // TODO: Check for constant.
            $row_id_token
        );
    }

    public function onExecute(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token
    ): mixed {
        $record_id = $this->http_service->resolveRowParameter($row_id_token->getName());

        $this->ctrl->setParameterByClass(
            \ilBookingScheduleGUI::class,
            'schedule_id',
            $record_id
        );
        $this->ctrl->redirectByClass(
            \ilBookingScheduleGUI::class,
            'edit'
        );
    }

    /**
     * @param ScheduleRecord $record
     */
    public function allowActionForRecord(mixed $record): bool
    {
        return true;
    }

    public function getSelectionErrorMessage(): ?string
    {
        return null;
    }
}
