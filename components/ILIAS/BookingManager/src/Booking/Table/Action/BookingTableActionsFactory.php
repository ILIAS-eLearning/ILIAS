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

namespace ILIAS\BookingManager\Booking;

use ilCtrlInterface;
use ilGlobalTemplateInterface;
use ILIAS\BookingManager\Access\AccessManager;
use ILIAS\BookingManager\Bookings\Table\Action\BookingsTableCancelAction;
use ILIAS\BookingManager\Bookings\Table\Action\BookingsTableDeleteAction;
use ILIAS\BookingManager\Bookings\Table\Action\BookingsTableMailAction;
use ILIAS\BookingManager\Common\HttpService;
use ILIAS\BookingManager\Common\Table\TableActions;
use ILIAS\BookingManager\Common\Table\TableActionsFactory;
use ILIAS\BookingManager\Reservations\ReservationDBRepository;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ilLanguage;
use ilObjBookingPool;

class BookingTableActionsFactory implements TableActionsFactory
{
    public const string ACTION_CANCEL = 'cancel';

    public const string ACTION_DELETE = 'delete';

    public const string ACTION_MAIL = 'mail';

    public function __construct(
        protected readonly ilCtrlInterface $ctrl,
        protected readonly ilLanguage $lng,
        protected readonly ilGlobalTemplateInterface $tpl,
        protected readonly UIFactory $ui_factory,
        protected readonly UIRenderer $ui_renderer,
        protected readonly Refinery $refinery,
        protected readonly HttpService $http,
        protected readonly AccessManager $access,
        protected readonly ReservationDBRepository $reservation_repository,
        protected readonly ilObjBookingPool $booking_pool,
        protected readonly array $bookings,
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
                self::ACTION_CANCEL => $this->getCancelAction(),
                self::ACTION_DELETE => $this->getDeleteAction(),
                self::ACTION_MAIL => $this->getMailAction(),
            ]
        );
    }

    protected function getCancelAction(): BookingsTableCancelAction
    {
        return new BookingsTableCancelAction(
            $this->access,
            $this->ui_factory,
            $this->ui_renderer,
            $this->lng,
            $this->reservation_repository,
            $this->http,
            $this->tpl,
            $this->ctrl,
            $this->booking_pool,
            $this->bookings
        );
    }

    protected function getDeleteAction(): BookingsTableDeleteAction
    {
        return new BookingsTableDeleteAction(
            $this->access,
            $this->ui_factory,
            $this->ui_renderer,
            $this->lng,
            $this->reservation_repository,
            $this->http,
            $this->tpl,
            $this->ctrl,
            $this->booking_pool,
            $this->bookings
        );
    }

    protected function getMailAction(): BookingsTableMailAction
    {
        return new BookingsTableMailAction(
            $this->access,
            $this->ui_factory,
            $this->ui_renderer,
            $this->lng,
            $this->reservation_repository,
            $this->http,
            $this->tpl,
            $this->ctrl,
            $this->booking_pool,
            $this->bookings
        );
    }
}
