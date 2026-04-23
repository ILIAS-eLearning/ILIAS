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

namespace ILIAS\BookingManager\BookableItem;

use ilBookingObjectGUI;
use ilBookingReservation;
use ilDateTime;
use ILIAS\BookingManager\Access\AccessManager;
use ILIAS\BookingManager\Common\Table\TableAction;
use ILIAS\BookingManager\HttpService;
use ILIAS\UI\Component\Table\Action\Action;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ilCtrlInterface;
use ilLanguage;

class BookableItemTableAssignParticipantAction implements TableAction
{
    public const string ACTION_ID = 'assign_participant';

    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly ilLanguage $lng,
        private readonly AccessManager $access,
        private readonly ilCtrlInterface $ctrl,
        private readonly HttpService $http_service,
        private readonly ilBookingObjectGUI $object_gui,
        private readonly int $ref_id,
        private readonly string $process_with,
        private readonly string $process_without,
        private readonly BookableItemTableData $data,
    ) {
    }

    public function getActionId(): string
    {
        return self::ACTION_ID;
    }

    public function isAvailable(): bool
    {
        return $this->object_gui->isManagementActivated()
            && $this->access->canManageAllReservations($this->ref_id);
    }

    public function getTableAction(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token
    ): Action {
        return $this->ui_factory->table()->action()->single(
            $this->lng->txt('book_assign_participant'),
            $url_builder
                ->withParameter($action_token, self::ACTION_ID)
                ->withParameter($action_type_token, 'assign'),
            $row_id_token
        );
    }

    public function onExecute(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token
    ): mixed {
        $row_id = (string) $this->http_service->resolveRowParameter($row_id_token->getName());
        $p = BookableItemTableData::parseRowIdForBulk($row_id);
        if ($p === null) {
            $this->ctrl->redirect($this->object_gui, 'render');
            return null;
        }
        $oid = (int) $p['object_id'];
        $this->ctrl->setParameter($this->object_gui, 'object_id', (string) $oid);

        if ($this->data->isSchedulePool()) {
            if (empty($p['is_slot']) || $p['from'] === null || $p['to'] === null) {
                $this->ctrl->redirect($this->object_gui, 'render');
                return null;
            }
            $day_seed = (new ilDateTime((int) $p['from'], IL_CAL_UNIX))->get(IL_CAL_DATE);
            $this->ctrl->setParameter($this->object_gui, 'sseed', $day_seed);
            $this->ctrl->setParameterByClass($this->process_with, 'sseed', $day_seed);
            $this->ctrl->redirectByClass($this->process_with, 'assignParticipants');
            return null;
        }

        $this->ctrl->redirectByClass($this->process_without, 'assignParticipants');
        return null;
    }

    public function allowActionForRecord(mixed $record): bool
    {
        if (!\is_array($record) || !isset($record['row'], $record['may_assign'], $record['current_user_bookings'])
            || !(bool) $record['may_assign']
            || isset($record['row']['full_up'])) {
            return false;
        }
        $r = $record['row'];
        $not_yet = isset($r['not_yet']) ? (string) $r['not_yet'] : '';
        if ($this->data->isSchedulePool()) {
            return $this->data->rowStillAllowsUserBulkBook($r, (int) $record['current_user_bookings']);
        }
        $n = 0;
        foreach ($this->data->reservationsForObject((int) $r['booking_object_id']) as $i) {
            if ((int) $i['status'] !== ilBookingReservation::STATUS_CANCELLED) {
                $n++;
            }
        }
        return (int) $r['nr_items'] > $n && $not_yet === '';
    }

    public function getSelectionErrorMessage(): ?string
    {
        return null;
    }
}
