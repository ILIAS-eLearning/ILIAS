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

use ilBookingReservationsGUI;
use ilCtrlInterface;
use ILIAS\BookingManager\Access\AccessManager;
use ILIAS\BookingManager\Common\HttpService;
use ILIAS\BookingManager\Common\Table\TableAction;
use ILIAS\UI\Component\Table\Action\Action;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ilLanguage;
use ilObjUser;
use ilObjBookingPool;

class BookableItemTableBookingsAction implements TableAction
{
    public const string ACTION_ID = 'bookings';
    public const string ACTION_LABEL = 'book_log';

    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly ilLanguage $lng,
        private readonly ilCtrlInterface $ctrl,
        private readonly HttpService $http,
        private readonly AccessManager $access,
        private readonly ilObjUser $user,
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
        return $this->ui_factory->table()->action()->single(
            $this->lng->txt(self::ACTION_LABEL),
            $url_builder
                ->withParameter($action_token, self::ACTION_ID)
                ->withParameter($action_type_token, 'redirect'),
            $row_id_token
        );
    }

    public function onExecute(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token
    ): mixed {
        $row_id = (string) $this->http->resolveRowParameter($row_id_token->getName());
        $object_id = (int) explode('_', $row_id)[0];

        if ($object_id <= 0) {
            return null;
        }

        $this->ctrl->setParameterByClass(ilBookingReservationsGUI::class, 'object_id', (int) $object_id);

        if (!$this->access->canManageAllReservations($this->ref_id) && !$this->access->canReadPublicLog($this->ref_id)) {
            $this->ctrl->setParameterByClass(ilBookingReservationsGUI::class, 'user_id', (int) $this->user->getId());
        }

        if ($this->pool->getScheduleType() === ilObjBookingPool::TYPE_FIX_SCHEDULE) {
            $this->ctrl->setParameterByClass(ilBookingReservationsGUI::class, 'period_from', explode('_', $row_id)[1] ?? null);
            $this->ctrl->setParameterByClass(ilBookingReservationsGUI::class, 'period_to', explode('_', $row_id)[2] ?? null);
        }

        $this->ctrl->redirectByClass(ilBookingReservationsGUI::class, '');
        return null;
    }

    public function allowActionForRecord(mixed $record): bool
    {
        $has_reservations = $record['has_reservations'] ?? false;

        if (
            $this->access->canManageAllReservations($this->ref_id)
            || $this->access->canReadPublicLog($this->ref_id)
        ) {
            return $has_reservations;
        }

        if ($this->access->canManageOwnReservations($this->ref_id)) {
            return $has_reservations && ($record['has_user_booking'] ?? false);
        }

        return false;
    }
}
