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

namespace ILIAS\BookingManager\Participant\Table\Action;

use ilCtrlInterface;
use ilGlobalTemplateInterface;
use ILIAS\BookingManager\Access\AccessManager;
use ILIAS\BookingManager\Common\HttpService;
use ILIAS\BookingManager\Common\Table\TableAction;
use ILIAS\BookingManager\Common\Table\TableActions;
use ILIAS\BookingManager\Common\Table\TableActionsFactory;
use ILIAS\BookingManager\Participant\ParticipantRepository;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ilLanguage;

class ParticipantTableActionsFactory implements TableActionsFactory
{
    public const string ACTION_BOOK_FOR_PARTICIPANT = 'book_for_participant';

    public const string ACTION_DELETE = 'delete';

    public const string ACTION_EDIT_BOOKING = 'edit_booking';

    public function __construct(
        protected readonly ilCtrlInterface $ctrl,
        protected readonly ilLanguage $lng,
        protected readonly ilGlobalTemplateInterface $tpl,
        protected readonly UIFactory $ui_factory,
        protected readonly UIRenderer $ui_renderer,
        protected readonly Refinery $refinery,
        protected readonly HttpService $http,
        protected readonly AccessManager $access,
        protected readonly ParticipantRepository $participant_repository,
        protected readonly int $ref_id,
        protected readonly int $pool_id,
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
                self::ACTION_BOOK_FOR_PARTICIPANT => $this->getBookForParticipantAction(),
                self::ACTION_DELETE => $this->getDeleteAction(),
                self::ACTION_EDIT_BOOKING => $this->getEditBookingAction(),
            ]
        );
    }

    protected function getBookForParticipantAction(): TableAction
    {
        return new ParticipantTableBookForParticipantAction(
            $this->ui_factory,
            $this->lng,
            $this->access,
            $this->ctrl,
            $this->http,
            $this->ref_id,
            $this->pool_id,
        );
    }

    protected function getDeleteAction(): TableAction
    {
        return new ParticipantTableDeleteAction(
            $this->ui_factory,
            $this->ui_renderer,
            $this->lng,
            $this->access,
            $this->ctrl,
            $this->tpl,
            $this->http,
            $this->participant_repository,
            $this->ref_id,
            $this->pool_id,
        );
    }

    protected function getEditBookingAction(): TableAction
    {
        return new ParticipantTableEditBookingAction(
            $this->ui_factory,
            $this->lng,
            $this->access,
            $this->ctrl,
            $this->http,
            $this->ref_id,
            $this->pool_id,
        );
    }
}
