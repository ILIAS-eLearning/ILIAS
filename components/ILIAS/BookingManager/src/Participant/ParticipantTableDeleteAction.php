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

use ilGlobalTemplateInterface;
use ILIAS\BookingManager\Access\AccessManager;
use ILIAS\BookingManager\Common\TableActionModalTrait;
use ILIAS\BookingManager\Common\Table\TableAction;
use ILIAS\BookingManager\HttpService;
use ILIAS\UI\Component\MessageBox\MessageBox;
use ILIAS\UI\Component\Modal\InterruptiveItem\InterruptiveItem;
use ILIAS\UI\Component\Modal\Modal;
use ILIAS\UI\Component\Table\Action\Action;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ilLanguage;

use function array_map;

/**
 * @phpstan-type ParticipantRecord array{user_id: int, name: string, object_title: array<string>, obj_count: int, object_ids: array<int>}
 * @implements TableAction<ParticipantRecord>
 */
class ParticipantTableDeleteAction implements TableAction
{
    use TableActionModalTrait;

    public const ACTION_ID = 'delete';

    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly ilLanguage $lng,
        private readonly AccessManager $access,
        private readonly ilGlobalTemplateInterface $tpl,
        private readonly HttpService $http_service,
        private readonly ParticipantRepository $participant_repository,
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
        return $this->ui_factory->table()->action()->standard(
            $this->lng->txt('book_remove_participants'),
            $url_builder
                ->withParameter($action_token, self::ACTION_ID)
                ->withParameter($action_type_token, self::SHOW_MODAL_ACTION),
            $row_id_token
        )->withAsync();
    }

    /**
     * @param ParticipantRecord $record
     */
    public function allowActionForRecord(mixed $record): bool
    {
        return true;
    }

    public function getModal(
        URLBuilder $url_builder,
        array $selected_records,
        bool $all_records_selected
    ): ?Modal {
        return $this->ui_factory->modal()->interruptive(
            $this->lng->txt('confirm'),
            $this->lng->txt('book_confirm_remove_participant'),
            $url_builder->buildURI()->__toString()
        )->withAffectedItems(
            array_map(
                fn(array $record): InterruptiveItem => $this->ui_factory->modal()->interruptiveItem()->standard(
                    (string) $record['user_id'],
                    (string) ($record['name'] ?? '')
                ),
                $selected_records
            )
        )->withActionButtonLabel($this->lng->txt('remove'));
    }

    public function onSubmit(
        URLBuilder $url_builder,
        array $selected_records,
        bool $all_records_selected
    ): ?Modal {
        if (!$this->access->canManageParticipants($this->ref_id)) {
            $this->tpl->setOnScreenMessage(
                \ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE,
                $this->lng->txt('no_permission'),
                true
            );
            return null;
        }

        foreach ($selected_records as $record) {
            $this->deleteParticipant((int) $record['user_id']);
        }

        $this->tpl->setOnScreenMessage(
            \ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS,
            $this->lng->txt('book_participant_removed'),
            true
        );

        return null;
    }

    public function getSelectionErrorMessage(): ?string
    {
        return $this->lng->txt('book_table_no_valid_selection');
    }

    protected function resolveRecords(array $selected_ids): array
    {
        $all_participants = \ilBookingParticipant::getList($this->pool_id);

        if ($selected_ids === []) {
            return array_values($all_participants);
        }

        $records = [];
        foreach ($selected_ids as $user_id) {
            $key = $this->pool_id . '_' . $user_id;
            if (isset($all_participants[$key])) {
                $records[] = $all_participants[$key];
            }
        }

        return $records;
    }

    private function deleteParticipant(int $user_id): void
    {
        $this->participant_repository->delete($user_id, $this->pool_id);
    }
}
