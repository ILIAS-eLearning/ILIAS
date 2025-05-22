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

use ILIAS\Test\Logging\TestAdministrationInteractionTypes;
use ILIAS\Test\Logging\AdditionalInformationGenerator;
use ILIAS\Language\Language;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Modal\Modal;
use ILIAS\UI\Component\Table\Action\Action;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\Refinery\Factory as Refinery;
use Psr\Http\Message\ServerRequestInterface;

class ParticipantTableExtraTimeAction implements TableAction
{
    public const ACTION_ID = 'extratime';

    public function __construct(
        private readonly Language $lng,
        private readonly Refinery $refinery,
        private readonly \ilGlobalTemplateInterface $tpl,
        private readonly UIFactory $ui_factory,
        private readonly ParticipantRepository $participant_repository,
        private readonly \ilObjUser $current_user,
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
        return $this->test_obj->getEnableProcessingTime()
            && $this->test_access->checkManageParticipantsAccess();
    }

    public function getTableAction(
        URLBuilder $url_builder,
        URLBuilderToken $row_id_token,
        URLBuilderToken $action_token,
        URLBuilderToken $action_type_token
    ): Action {
        return $this->ui_factory->table()->action()->standard(
            $this->lng->txt(self::ACTION_ID),
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
        $has_different_extra_time = $this->resolveHasDifferentExtraTime($selected_participants);
        $participant_rows = array_map(
            fn(Participant $participant) => sprintf(
                '%s, %s (%s)',
                $participant->getLastname(),
                $participant->getFirstname(),
                sprintf($this->lng->txt('already_added_extra_time'), $participant->getExtraTime())
            ),
            $selected_participants
        );

        return $this->ui_factory->modal()->roundtrip(
            $this->lng->txt('extratime'),
            [
                $this->ui_factory->messageBox()->info(
                    $this->lng->txt(
                        $this->resolveInfoMessage(
                            $selected_participants,
                            $all_participants_selected,
                            $has_different_extra_time
                        )
                    )
                ),
                $this->ui_factory->listing()->unordered($participant_rows)
            ],
            [
                'extra_time' => $this->ui_factory->input()->field()->numeric(
                    $this->lng->txt('extratime')
                )->withRequired(true)
                ->withAdditionalTransformation($this->refinery->int()->isGreaterThan(0))
                ->withValue(0)
                ->withByline(
                    $this->lng->txt('extra_time_byline')
                )
            ],
            $url_builder->buildURI()->__toString()
        )->withSubmitLabel($this->lng->txt('add'));
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
        $modal = $this->getModal(
            $url_builder,
            $selected_participants,
            $all_participants_selected
        )->withRequest($request);

        $data = $modal->getData();
        if ($data === null) {
            return $modal->withOnLoad($modal->getShowSignal());
        }

        $this->saveExtraTime($selected_participants, $data['extra_time']);

        $this->tpl->setOnScreenMessage(
            \ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS,
            $this->lng->txt('extratime_added'),
            true
        );
        return null;
    }

    public function allowActionForRecord(Participant $record): bool
    {
        return $record->getUserId() !== ANONYMOUS_USER_ID;
    }

    private function resolveInfoMessage(
        array $selected_participants,
        bool $all_participants_selected,
        bool $has_different_extra_time
    ): string {
        if ($all_participants_selected) {
            return 'extra_time_for_all_participants';
        }

        if (count($selected_participants) === 1) {
            return 'extra_time_for_single_participant';
        }
        if ($has_different_extra_time) {
            return 'extra_time_for_selected_participants_different';
        }

        return 'extra_time_for_selected_participants';
    }

    /**
     * @param array<ilTestParticipant> $participants
     */
    private function resolveHasDifferentExtraTime(array $participants): bool
    {
        return count(array_unique(array_map(
            fn(Participant $participant) => $participant->getExtraTime(),
            $participants
        ))) > 1;
    }

    /**
     * @param array<Participant> $participants
     */
    public function saveExtraTime(array $participants, int $minutes): void
    {
        foreach ($participants as $participant) {
            $this->participant_repository->updateExtraTime($participant->withAddedExtraTime($minutes));
        }

        if ($this->test_obj->getTestLogger()->isLoggingEnabled()) {
            $this->test_obj->getTestLogger()->logTestAdministrationInteraction(
                $this->test_obj->getTestLogger()->getInteractionFactory()->buildTestAdministrationInteraction(
                    $this->test_obj->getRefId(),
                    $this->current_user->getId(),
                    TestAdministrationInteractionTypes::EXTRA_TIME_ADDED,
                    [
                        AdditionalInformationGenerator::KEY_USERS => array_map(
                            fn(Participant $participant) => $participant->getUserId(),
                            $participants
                        ),
                        AdditionalInformationGenerator::KEY_TEST_ADDED_PROCESSING_TIME => $minutes
                    ]
                )
            );
        }
    }

    public function getSelectionErrorMessage(): ?string
    {
        return null;
    }
}
