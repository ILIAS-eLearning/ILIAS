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
class ParticipantTableEditBookingAction implements TableAction
{
    public const ACTION_ID = 'edit_booking';

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
            $this->lng->txt('book_deassign'),
            $url_builder
                ->withParameter($action_token, self::ACTION_ID)
                ->withParameter($action_type_token, 'edit'),
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

        $bp = new \ilObjBookingPool($this->pool_id, false);
        $obj_count = $this->getObjectCountForUser((int) $user_id);

        if ($obj_count === 1 && $bp->getScheduleType() === \ilObjBookingPool::TYPE_NO_SCHEDULE) {
            // Single object, no schedule - direct deassign
            $object_ids = $this->getObjectIdsForUser((int) $user_id);
            if (!empty($object_ids)) {
                $this->ctrl->setParameterByClass('ilbookingreservationsgui', 'bkusr', (string) $user_id);
                $this->ctrl->setParameterByClass('ilbookingreservationsgui', 'object_id', (string) $object_ids[0]);
                $this->ctrl->setParameterByClass('ilbookingreservationsgui', 'part_view', \ilBookingParticipantGUI::PARTICIPANT_VIEW);
                $this->ctrl->redirectByClass('ilbookingreservationsgui', 'rsvConfirmCancelUser');
            }
        } else {
            // Multiple objects or schedule - show log
            $this->ctrl->setParameterByClass('ilbookingreservationsgui', 'user_id', (string) $user_id);
            $this->ctrl->redirectByClass('ilbookingreservationsgui', 'log');
        }

        return null;
    }

    /**
     * @param ParticipantRecord $record
     */
    public function allowActionForRecord(mixed $record): bool
    {
        $obj_count = (int) ($record['obj_count'] ?? 0);
        return $obj_count > 0;
    }

    public function getSelectionErrorMessage(): ?string
    {
        return null;
    }

    private function getObjectCountForUser(int $user_id): int
    {
        $data = \ilBookingParticipant::getList($this->pool_id, ['user_id' => $user_id]);
        $user_data = $data[$this->pool_id . '_' . $user_id] ?? null;
        return (int) ($user_data['obj_count'] ?? 0);
    }

    /**
     * @return array<int>
     */
    private function getObjectIdsForUser(int $user_id): array
    {
        $data = \ilBookingParticipant::getList($this->pool_id, ['user_id' => $user_id]);
        $user_data = $data[$this->pool_id . '_' . $user_id] ?? null;
        return $user_data['object_ids'] ?? [];
    }
}
