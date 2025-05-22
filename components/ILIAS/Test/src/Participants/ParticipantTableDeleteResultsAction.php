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

namespace ILIAS\Test\Participants;

use ILIAS\Language\Language;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Modal\Modal;
use ILIAS\UI\Component\Table\Action\Action;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use Psr\Http\Message\ServerRequestInterface;

class ParticipantTableDeleteResultsAction implements TableAction
{
    public const ACTION_ID = 'delete_results';

    public function __construct(
        private readonly Language $lng,
        private readonly \ilGlobalTemplateInterface $tpl,
        private readonly UIFactory $ui_factory,
        private readonly \ilDBInterface $db,
        private readonly \ilTestParticipantAccessFilterFactory $participant_access_filter_factory,
        private readonly \ilTestAccess $test_access,
        private readonly \ilObjTest $test_obj
    ) {
    }

    public function getActionId(): string
    {
        return self::ACTION_ID;
    }

    public function isAvailable(): bool
    {
        return $this->test_access->checkManageParticipantsAccess()
            && $this->test_obj->evalTotalPersons() > 0;
    }

    public function getTableAction(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token
    ): Action {
        return $this->ui_factory->table()->action()->standard(
            $this->lng->txt('delete_user_data'),
            $url_builder
                ->withParameter($action_token, self::ACTION_ID)
                ->withParameter($action_type_token, ParticipantTableActions::SHOW_ACTION),
            $row_id_token
        )->withAsync();
    }

    public function getModal(
        URLBuilder $url_builder,
        array $selected_participants,
        bool $all_participants_selected
    ): ?Modal {
        return $this->ui_factory->modal()->interruptive(
            $this->lng->txt('confirm'),
            $this->resolveMessage($all_participants_selected),
            $url_builder->buildURI()->__toString()
        )->withAffectedItems(
            array_map(
                fn(Participant $v) => $this->ui_factory->modal()->interruptiveItem()->standard(
                    (string) $v->getUserId(),
                    $this->test_obj->getAnonymity()
                        ? $this->lng->txt('anonymous')
                        : \ilObjUser::_lookupFullname($v->getUserId())
                ),
                $selected_participants
            )
        );
    }

    public function onSubmit(
        URLBuilder $url_builder,
        ServerRequestInterface $request,
        array $selected_participants,
        bool $all_participants_selected
    ): ?Modal {
        if (!$this->test_access->checkManageParticipantsAccess()) {
            $this->tpl->setOnScreenMessage(
                \ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE,
                $this->lng->txt('no_permission'),
                true
            );
            return null;
        }

        $access_filter = $this->participant_access_filter_factory
            ->getManageParticipantsUserFilter($this->test_obj->getRefId());
        $participant_data = new \ilTestParticipantData($this->db, $this->lng);
        $participant_data->setParticipantAccessFilter($access_filter);
        $participant_data->setActiveIdsFilter(
            array_map(
                static fn(Participant $v): int => $v->getActiveId(),
                $selected_participants
            )
        );
        $participant_data->load($this->test_obj->getTestId());
        $this->test_obj->removeTestResults($participant_data);

        $this->tpl->setOnScreenMessage(
            \ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS,
            $this->lng->txt('tst_selected_user_data_deleted'),
            true
        );
        return null;
    }

    public function allowActionForRecord(Participant $record): bool
    {
        return $record->getActiveId() !== null;
    }

    private function resolveMessage(bool $all_participants_selected): string
    {
        if ($all_participants_selected) {
            return $this->lng->txt('delete_all_user_data_confirmation');
        }

        return $this->lng->txt('delete_selected_user_data_confirmation');
    }

    public function getSelectionErrorMessage(): ?string
    {
        return $this->lng->txt('delete_result_no_valid_participants_selected');
    }
}
