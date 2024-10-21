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

use ILIAS\Test\Logging\AdditionalInformationGenerator;
use ILIAS\Test\Logging\TestAdministrationInteractionTypes;
use ILIAS\Language\Language;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Modal\Modal;
use ILIAS\UI\Component\Table\Action\Action;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use Psr\Http\Message\ServerRequestInterface;

class ParticipantTableFinishTestAction implements TableAction
{
    public const ACTION_ID = 'finish_test';

    public function __construct(
        private readonly Language $lng,
        private readonly \ilGlobalTemplateInterface $tpl,
        private readonly UIFactory $ui_factory,
        private readonly \ilDBInterface $db,
        private readonly \ilTestProcessLockerFactory $process_locker_factory,
        private readonly \ilObjUser $user,
        private readonly \ilObjTest $test_obj
    ) {
    }

    public function getActionId(): string
    {
        return self::ACTION_ID;
    }

    public function isEnabled(): bool
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
        $modal = $this->ui_factory->modal()->interruptive(
            $this->lng->txt('finish_test'),
            $this->resolveMessage($selected_participants),
            $url_builder->buildURI()->__toString()
        )->withActionButtonLabel($this->lng->txt('finish_test'));

        if (count($selected_participants) > 1) {
            $modal = $modal->withAffectedItems(
                array_map(
                    fn(Participant $participant) => $this->ui_factory->modal()->interruptiveItem()->standard(
                        (string) $participant->getUserId(),
                        sprintf(
                            '%s, %s',
                            $participant->getLastname(),
                            $participant->getFirstname()
                        )
                    ),
                    $selected_participants
                )
            );
        }

        return $modal;
    }

    public function onSubmit(
        URLBuilder $url_builder,
        ServerRequestInterface $request,
        array $selected_participants,
        bool $all_participants_selected
    ): ?Modal {
        // This is required here because of late test object binding
        $test_session_factory = new \ilTestSessionFactory(
            $this->test_obj,
            $this->db,
            $this->user
        );

        foreach ($selected_participants as $participant) {
            $process_locker = $this->process_locker_factory->withContextId($participant->getActiveId())->getLocker();

            $test_pass_finisher = new \ilTestPassFinishTasks(
                $test_session_factory->getSession($participant->getActiveId()),
                $this->test_obj->getTestId()
            );
            $test_pass_finisher->performFinishTasks($process_locker);
        }

        $logger = $this->test_obj->getTestLogger();
        if ($logger->isLoggingEnabled()) {
            $logger->logTestAdministrationInteraction(
                $logger->getInteractionFactory()->buildTestAdministrationInteraction(
                    $this->test_obj->getRefId(),
                    $this->user->getId(),
                    TestAdministrationInteractionTypes::TEST_RUN_OF_PARTICIPANT_CLOSED,
                    [
                        AdditionalInformationGenerator::KEY_USERS => array_map(
                            fn(Participant $participant) => $participant->getUserId(),
                            $selected_participants
                        )
                    ]
                )
            );
        }

        $this->tpl->setOnScreenMessage(
            \ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS,
            $this->lng->txt('test_attempts_finished'),
            true
        );
        return null;
    }

    public function allowActionForRecord(Participant $record): bool
    {
        return $record->hasUnfinishedAttempts();
    }

    private function resolveMessage(
        array $selected_participants,
        bool $all_participants_selected
    ): string {
        if ($all_participants_selected) {
            return $this->lng->txt('finish_test_all');
        }

        if (count($selected_participants) === 1) {
            return sprintf(
                $this->lng->txt('finish_test_single'),
                sprintf(
                    '%s, %s',
                    $selected_participants[0]->getLastname(),
                    $selected_participants[0]->getFirstname()
                )
            );
        }

        return $this->lng->txt('finish_test_multiple');
    }
}
