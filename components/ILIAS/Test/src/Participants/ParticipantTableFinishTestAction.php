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
use ILIAS\Test\RequestDataCollector;
use ILIAS\Test\ResponseHandler;
use ILIAS\UI\Component\Input\Container\Form\Standard;
use ILIAS\UI\Component\Modal\Modal;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Refinery\Factory as Refinery;
use ilTestParticipantsGUI;

class ParticipantTableFinishTestAction extends ParticipantTableModalAction
{
    private const ACTION_ID = 'finish_test';

    public function __construct(
        \ilCtrlInterface $ctrl,
        \ilLanguage $lng,
        \ilGlobalTemplateInterface $template,
        UIFactory $ui_factory,
        UIRenderer $ui_renderer,
        Refinery $refinery,
        RequestDataCollector $test_request,
        ResponseHandler $test_response,
        ParticipantRepository $repository,
        private readonly \ilDBInterface $db,
        private readonly \ilTestProcessLockerFactory $process_locker_factory,
        private readonly \ilObjUser $user
    ) {
        parent::__construct(
            $ctrl,
            $lng,
            $template,
            $ui_factory,
            $ui_renderer,
            $refinery,
            $test_request,
            $test_response,
            $repository
        );
    }

    public function getActionId(): string
    {
        return self::ACTION_ID;
    }

    protected function getModal(URLBuilder $url_builder, array|string $selected_participants): Modal|Standard
    {
        $participants = $selected_participants !== 'ALL_OBJECTS'
            ? $this->resolveSelectedParticipants($selected_participants)
            : [];

        $modal = $this->ui_factory->modal()->interruptive(
            $this->lng->txt('finish_test'),
            $this->resolveMessage($selected_participants, $participants),
            (string) $url_builder->buildURI()
        )->withActionButtonLabel($this->lng->txt('finish_test'));

        if (count($participants) > 1) {
            $modal = $modal->withAffectedItems(
                array_map(
                    fn(Participant $participant) => $this->ui_factory->modal()->interruptiveItem()->standard(
                        (string) $participant->getUsrId(),
                        sprintf(
                            '%s, %s',
                            $participant->getLastname(),
                            $participant->getFirstname()
                        )
                    ),
                    $participants
                )
            );
        }

        return $modal;
    }

    protected function onSubmit(Standard|Modal $modal, array $participants): void
    {
        // This is required here because of late test object binding
        $test_session_factory = new \ilTestSessionFactory(
            $this->test_object,
            $this->db,
            $this->user
        );

        foreach ($participants as $participant) {
            $process_locker = $this->process_locker_factory->withContextId($participant->getActiveId())->getLocker();

            $test_pass_finisher = new \ilTestPassFinishTasks(
                $test_session_factory->getSession($participant->getActiveId()),
                $this->test_object->getTestId()
            );
            $test_pass_finisher->performFinishTasks($process_locker);
        }

        $logger = $this->test_object->getTestLogger();
        if ($logger->isLoggingEnabled()) {
            $logger->logTestAdministrationInteraction(
                $logger->getInteractionFactory()->buildTestAdministrationInteraction(
                    $this->test_object->getRefId(),
                    $this->user->getId(),
                    TestAdministrationInteractionTypes::TEST_RUN_OF_PARTICIPANT_CLOSED,
                    [
                        AdditionalInformationGenerator::KEY_USERS => array_map(
                            fn(Participant $participant) => $participant->getUsrId(),
                            $participants
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
    }

    private function resolveMessage(array|string $selected_participants, array $participants): string
    {
        if ($selected_participants === 'ALL_OBJECTS') {
            return $this->lng->txt('finish_test_all');
        }

        if (count($participants) === 0) {
            return $this->lng->txt('no_valid_participant_selection');
        }

        if (count($participants) === 1) {
            return sprintf(
                $this->lng->txt('finish_test_single'),
                sprintf(
                    '%s, %s',
                    $participants[0]->getLastname(),
                    $participants[0]->getFirstname()
                )
            );
        }

        return $this->lng->txt('finish_test_multiple');
    }

    protected function allowActionForRecord(Participant $record): bool
    {
        return $record->hasUnfinishedPasses();
    }
}
