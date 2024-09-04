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

use ILIAS\UI\Component\Input\Container\Form\Standard;
use ILIAS\UI\Component\Modal\Modal;
use ILIAS\UI\URLBuilder;

use function array_map;
use function count;
use function sprintf;

class ParticipantTableFinishTestAction extends ParticipantTableModalAction
{
    private const ACTION_ID = 'finish_test';

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
        );

        if (count($participants) > 1) {
            $modal = $modal->withAffectedItems(
                array_map(
                    fn(Participant $participant) => $this->ui_factory->modal()->interruptiveItem()->standard(
                        $participant->getUsrId(),
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

    protected function onSubmit(Standard|Modal $modal, array|string $selected_participants): void
    {
        // TODO: Implement onSubmit() method.
    }

    private function resolveMessage(array|string $selected_participants, array $participants): string
    {
        if ($selected_participants === 'ALL_OBJECTS') {
            return $this->lng->txt('finish_test_all');
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
}
