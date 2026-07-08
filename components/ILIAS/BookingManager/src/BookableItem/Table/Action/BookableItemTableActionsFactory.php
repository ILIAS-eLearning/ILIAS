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

use ilCtrlInterface;
use ilGlobalTemplateInterface;
use ILIAS\BookingManager\Access\AccessManager;
use ILIAS\BookingManager\Common\HttpService;
use ILIAS\BookingManager\Common\Table\TableAction;
use ILIAS\BookingManager\Common\Table\TableActions;
use ILIAS\BookingManager\Common\Table\TableActionsFactory;
use ILIAS\BookingManager\BookingProcess\BookingProcessManager;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ilLanguage;
use ilObjBookingPool;
use ilObjUser;

class BookableItemTableActionsFactory implements TableActionsFactory
{
    public const string ACTION_BOOK = 'book';
    public const string ACTION_BOOK_FOR_PARTICIPANT = 'book_for_participant';
    public const string ACTION_EDIT = 'edit';
    public const string ACTION_BOOKINGS = 'bookings';
    public const string ACTION_DELETE = 'delete';
    public const string ACTION_CANCEL_BOOKING = 'cancel_booking';

    public function __construct(
        protected readonly ilCtrlInterface $ctrl,
        protected readonly ilLanguage $lng,
        protected readonly ilGlobalTemplateInterface $tpl,
        protected readonly UIFactory $ui_factory,
        protected readonly UIRenderer $ui_renderer,
        protected readonly Refinery $refinery,
        protected readonly HttpService $http,
        protected readonly AccessManager $access,
        protected readonly ilObjBookingPool $pool,
        protected readonly BookingProcessManager $process_manager,
        protected readonly ilObjUser $user,
        protected readonly int $ref_id,
        protected readonly bool $active_management,
        protected readonly int $booking_context_obj_id,
        protected readonly array $bookable_items,
    ) {
    }

    public function getTableActions(): TableActions
    {
        return new TableActions(
            $this->ctrl,
            $this->lng,
            $this->tpl,
            $this->ui_factory,
            $this->ui_renderer,
            $this->refinery,
            $this->http,
            [
                self::ACTION_BOOK => $this->getBookAction(),
                self::ACTION_BOOK_FOR_PARTICIPANT => $this->getBookForParticipantAction(),
                self::ACTION_EDIT => $this->getEditAction(),
                self::ACTION_BOOKINGS => $this->getBookingsAction(),
                self::ACTION_DELETE => $this->getDeleteAction(),
                self::ACTION_CANCEL_BOOKING => $this->getCancelBookingAction(),
            ]
        );
    }

    protected function getBookAction(): TableAction
    {
        return new BookableItemTableBookAction(
            $this->ui_factory,
            $this->ui_renderer,
            $this->tpl,
            $this->lng,
            $this->http,
            $this->refinery,
            $this->ctrl,
            $this->access,
            $this->pool,
            $this->process_manager,
            $this->user,
            $this->ref_id,
            $this->booking_context_obj_id,
            $this->bookable_items,
        );
    }

    protected function getBookForParticipantAction(): TableAction
    {
        return new BookableItemTableBookForParticipantAction(
            $this->ui_factory,
            $this->lng,
            $this->ctrl,
            $this->http,
            $this->refinery,
            $this->access,
            $this->pool,
            $this->ref_id,
            $this->active_management,
        );
    }

    protected function getEditAction(): TableAction
    {
        return new BookableItemTableEditAction(
            $this->ui_factory,
            $this->lng,
            $this->ctrl,
            $this->http,
            $this->access,
            $this->ref_id,
            $this->active_management,
        );
    }

    protected function getBookingsAction(): TableAction
    {
        return new BookableItemTableBookingsAction(
            $this->ui_factory,
            $this->lng,
            $this->ctrl,
            $this->http,
            $this->access,
            $this->user,
            $this->pool,
            $this->ref_id,
            $this->active_management,
        );
    }

    protected function getDeleteAction(): TableAction
    {
        return new BookableItemTableDeleteAction(
            $this->ui_factory,
            $this->ui_renderer,
            $this->lng,
            $this->tpl,
            $this->http,
            $this->access,
            $this->pool,
            $this->ref_id,
            $this->active_management,
        );
    }

    protected function getCancelBookingAction(): TableAction
    {
        return new BookableItemTableCancelBookingAction(
            $this->ui_factory,
            $this->ui_renderer,
            $this->lng,
            $this->tpl,
            $this->http,
            $this->refinery,
            $this->access,
            $this->pool,
            $this->user,
            $this->ref_id,
            $this->active_management,
            $this->bookable_items,
        );
    }
}
