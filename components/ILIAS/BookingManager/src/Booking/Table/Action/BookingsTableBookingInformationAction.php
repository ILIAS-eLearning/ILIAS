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
use ILIAS\BookingManager\Common\HttpService;
use ILIAS\BookingManager\Common\Table\TableAction;
use ILIAS\Language\Language;
use ILIAS\UI\Component\Table\Action\Action;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;

class BookingsTableBookingInformationAction implements TableAction
{
    public const string ACTION_ID = 'booking_information';

    public const string ACTION_LABEL = 'book_post_booking_information';

    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly Language $lng,
        private readonly HttpService $http,
        private readonly ilGlobalTemplateInterface $tpl,
        private readonly ilCtrlInterface $ctrl,
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
        return true;
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
                ->withParameter($action_type_token, 'booking_information'),
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
        if ($row_parameters === HttpService::ALL_OBJECTS) {
            $selected_ids = null;
        } elseif (is_string($row_parameters)) {
            $selected_ids = [$row_parameters];
        } else {
            $selected_ids = $row_parameters;
        }

        $reservation_ids = $this->resolveRecords($selected_ids);

        if ($reservation_ids === []) {
            $this->tpl->setOnScreenMessage(
                ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE,
                $this->lng->txt('no_valid_selection'),
                true
            );
            return null;
        }

        $this->ctrl->setParameterByClass(
            ilBookingReservationsGUI::class,
            'rsv_ids',
            implode(';', $reservation_ids)
        );
        $this->ctrl->redirectByClass(ilBookingReservationsGUI::class, 'displayPostInfo');
        return null;
    }

    /**
     * @return int[]
     */
    protected function resolveRecords(?array $selected_ids = null): array
    {
        return array_map(
            'intval',
            $selected_ids ?? array_keys($this->bookings)
        );
    }
}
