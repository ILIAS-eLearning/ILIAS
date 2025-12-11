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

namespace ILIAS\BookingManager\Participant;

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
 * @phpstan-type ParticipantRecord array{user_id: int, name: string, object_title: array<string>, obj_count: int, object_ids: array<int>}
 * @implements TableAction<ParticipantRecord>
 */
class ParticipantTableBookForParticipantAction implements TableAction
{
    public const ACTION_ID = 'book_for_participant';

    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly ilLanguage $lng,
        private readonly AccessManager $access,
        private readonly ilCtrlInterface $ctrl,
        private readonly HttpService $http_service,
        private readonly int $ref_id,
        private readonly int $pool_id
    ) {
    }

    public function getActionId(): string
    {
        return self::ACTION_ID;
    }

    public function isAvailable(): bool
    {
        return $this->access->canManageParticipants($this->ref_id);
    }

    public function getTableAction(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token
    ): Action {
        return $this->ui_factory->table()->action()->single(
            $this->lng->txt('book_assign_object'),
            $url_builder
                ->withParameter($action_token, self::ACTION_ID)
                ->withParameter($action_type_token, 'book'),
            $row_id_token
        );
    }

    public function onExecute(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token
    ): mixed {
        $user_id = $this->http_service->resolveRowParameter($row_id_token->getName());

        $this->ctrl->setParameterByClass(
            \ilBookingParticipantGUI::class,
            'bkusr',
            (string) $user_id
        );
        $this->ctrl->redirectByClass(
            \ilBookingParticipantGUI::class,
            'assignObjects'
        );

        return null;
    }

    /**
     * @param ParticipantRecord $record
     */
    public function allowActionForRecord(mixed $record): bool
    {
        $obj_count = (int) ($record['obj_count'] ?? 0);
        $total_objects = \ilBookingObject::getNumberOfObjectsForPool($this->pool_id);
        return $obj_count < $total_objects;
    }

    public function getSelectionErrorMessage(): ?string
    {
        return null;
    }
}
