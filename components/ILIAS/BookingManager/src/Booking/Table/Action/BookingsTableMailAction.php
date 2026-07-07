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

namespace ILIAS\BookingManager\Bookings\Table\Action;

use ilBookingReservationsGUI;
use ilCtrlInterface;
use ilGlobalTemplateInterface;
use ILIAS\BookingManager\Access\AccessManager;
use ILIAS\BookingManager\Common\HttpService;
use ILIAS\BookingManager\Common\Table\TableAction;
use ILIAS\BookingManager\Reservations\ReservationDBRepository;
use ILIAS\Language\Language;
use ILIAS\UI\Component\Table\Action\Action;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ilLink;
use ilMailFormCall;
use ilObjBookingPool;
use ilObjUser;

class BookingsTableMailAction implements TableAction
{
    public const string ACTION_ID = 'mail';

    public const string ACTION_LABEL = 'book_mail_to_booker';

    public function __construct(
        private readonly AccessManager $access,
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly Language $lng,
        private readonly ReservationDBRepository $reservation_repository,
        private readonly HttpService $http,
        private readonly ilGlobalTemplateInterface $tpl,
        private readonly ilCtrlInterface $ctrl,
        private readonly ilObjBookingPool $booking_pool,
        private readonly array $bookings
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
        return true;
    }

    public function allowActionForRecord(mixed $record): bool
    {
        return $this->access->canManageAllReservations($this->booking_pool->getRefId());
    }

    public function getTableAction(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token
    ): Action {
        $action_type = $this->access->canManageAllReservations($this->booking_pool->getRefId()) ? 'standard' : 'single';
        return $this->ui_factory->table()->action()->$action_type(
            $this->lng->txt($this->getActionLabel()),
            $url_builder
                ->withParameter($action_token, self::ACTION_ID)
                ->withParameter($action_type_token, 'mail'),
            $row_id_token
        );
    }

    public function onExecute(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token
    ): mixed {
        $row_parameters = $this->http->resolveRowParameters($row_id_token->getName());
        $row_parameters = $row_parameters === HttpService::ALL_OBJECTS ? null : $row_parameters;
        $selected_user_ids = $this->resolveRecords($row_parameters);

        if ($selected_user_ids === []) {
            $this->tpl->setOnScreenMessage(
                ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE,
                $this->lng->txt('no_valid_selection'),
                true
            );
            return null;
        }

        $users = [];
        foreach ($selected_user_ids as $user_id) {
            $users[$user_id] = ilObjUser::_lookupLogin($user_id);
        }

        ilMailFormCall::setRecipients($users);
        $return_url = $this->ctrl->getLinkTargetByClass(ilBookingReservationsGUI::class, ilBookingReservationsGUI::DEFAULT_CMD);
        $this->ctrl->redirectToURL(ilMailFormCall::getRedirectTarget(
            $return_url,
            '',
            [],
            [
                'type' => 'new',
                'rcp_to' => implode(',', $users),
                ilMailFormCall::SIGNATURE_KEY => $this->createMailSignature()
            ]
        ));
        return null;
    }

    protected function resolveRecords(?array $selected_ids = null): array
    {
        $user_ids = array_map(
            fn(int $reservation_id): int => $this->bookings[$reservation_id]['user_id'],
            $selected_ids ?? array_keys($this->bookings)
        );
        return array_values(array_unique($user_ids));
    }

    private function createMailSignature(): string
    {
        // #16530 - see ilObjCourseGUI::createMailSignature
        $sig = chr(13) . chr(10) . chr(13) . chr(10) . chr(13) . chr(10);
        $sig .= "{$this->lng->txt('book_mail_permanent_link')}: ";
        $sig .= chr(13) . chr(10);
        $sig .= ilLink::_getLink($this->booking_pool->getRefId());
        return rawurlencode(base64_encode($sig));
    }
}
