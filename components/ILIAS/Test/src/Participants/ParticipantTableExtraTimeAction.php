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

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Test\RequestDataCollector;
use ILIAS\Test\ResponseHandler;
use ILIAS\UI\Component\Input\Container\Form\Standard;
use ILIAS\UI\Component\Modal\Modal;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\URLBuilder;

class ParticipantTableExtraTimeAction extends ParticipantTableModalAction
{
    public const ACTION_ID = 'extratime';

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
        private readonly \ilObjUser $user,
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
            $repository,
        );
    }

    public function getActionId(): string
    {
        return self::ACTION_ID;
    }

    public function isEnabled(): bool
    {
        return $this->test_object->getEnableProcessingTime();
    }

    protected function onSubmit(Standard|Modal $modal, array $participants): void
    {
        $data = $modal->getData();
        $this->test_object->addExtraTime($participants, $data['extra_time']);

        $this->tpl->setOnScreenMessage(
            \ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS,
            $this->lng->txt('extratime_added'),
            true
        );
    }

    protected function getModal(URLBuilder $url_builder, array|string $selected_participants): Modal|Standard
    {
        $participants = is_array($selected_participants) ? $this->resolveSelectedParticipants($selected_participants) : [];
        $has_different_extra_time = $this->resolveHasDifferentExtraTime($participants);

        $participants = is_array($selected_participants) ? $this->resolveSelectedParticipants($selected_participants) : [];
        $participant_rows = join("\n", array_map(
            fn(Participant $participant) => sprintf(
                '<p>%s, %s <em class="muted">(%s)</em></p>',
                $participant->getLastname(),
                $participant->getFirstname(),
                sprintf($this->lng->txt('already_added_extra_time'), $participant->getExtraTime())
            ),
            $participants
        ));

        return $this->ui_factory->modal()->roundtrip(
            $this->lng->txt('extratime'),
            [
                $this->ui_factory->messageBox()->info(
                    $this->lng->txt($this->resolveInfoMessage($selected_participants, $has_different_extra_time))
                ),
                $this->ui_factory->legacy("<ul>$participant_rows</ul>")
            ],
            [
                'extra_time' => $this->ui_factory->input()->field()->numeric(
                    $this->lng->txt('extratime')
                )->withByline(
                    $this->lng->txt('extra_time_byline')
                )
            ],
            (string) $url_builder->buildURI()
        )->withSubmitLabel($this->lng->txt('add'));
    }

    /**
     * @param array|string $selected_participants
     * @param bool  $has_different_extra_time
     *
     * @return string
     */
    private function resolveInfoMessage(
        mixed $selected_participants,
        bool $has_different_extra_time
    ): string {

        if ($selected_participants === 'ALL_OBJECTS') {
            return 'extra_time_for_all_participants';
        }

        if (count($selected_participants) === 0) {
            return $this->lng->txt('no_valid_participant_selection');
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
     * @param array $participants
     *
     * @return bool
     */
    private function resolveHasDifferentExtraTime(array $participants): bool
    {
        return count(array_unique(array_map(
            fn(\ilTestParticipant $participant) => $participant->getExtraTime(),
            $participants
        ))) > 1;
    }

    protected function allowActionForRecord(Participant $record): bool
    {
        return true;
        #return $this->test_object->getEnableProcessingTime();
    }
}
