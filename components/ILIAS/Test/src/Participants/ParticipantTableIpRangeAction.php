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
use ILIAS\UI\Component\Modal\RoundTrip;
use ILIAS\UI\URLBuilder;

class ParticipantTableIpRangeAction extends ParticipantTableModalAction
{
    public const ACTION_ID = 'client_ip_range';

    public function getActionId(): string
    {
        return self::ACTION_ID;
    }

    protected function onSubmit(Standard|Modal $modal, array $participants): void
    {
        $data = $modal->getData();
        $this->repository->updateIpRange($participants, $data['ip_range']);

        $this->tpl->setOnScreenMessage(
            \ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS,
            $this->lng->txt('ip_range_updated'),
            true
        );
    }

    protected function getModal(URLBuilder $url_builder, array|string $selected_participants): Modal|Standard
    {
        $valid_ip_constraint = $this->refinery->custom()->constraint(
            fn(?string $ip): bool => $ip === null || filter_var($ip, FILTER_VALIDATE_IP) !== false,
            $this->lng->txt('invalid_ip')
        );
        $participants = is_array($selected_participants) ? $this->resolveSelectedParticipants($selected_participants) : [];
        $participant_rows = join("\n", array_map(
            fn(Participant $participant) => sprintf(
                '<li>%s, %s</li>',
                $participant->getLastname(),
                $participant->getFirstname()
            ),
            $participants
        ));

        /** @var RoundTrip|Standard $modal */
        return $this->ui_factory->modal()->roundtrip(
            $this->lng->txt('client_ip_range'),
            [
                $this->ui_factory->messageBox()->info(
                    $this->lng->txt($this->resolveInfoMessage($selected_participants))
                ),
                $this->ui_factory->legacy("<ul>$participant_rows</ul>")
            ],
            [
                'ip_range' => $this->ui_factory->input()->field()->group([
                    'from' => $this->ui_factory->input()->field()->text(
                        $this->lng->txt('min_ip_label')
                    )->withAdditionalTransformation($valid_ip_constraint),
                    'to' => $this->ui_factory->input()->field()->text(
                        $this->lng->txt('max_ip_label'),
                        $this->lng->txt('ip_range_byline')
                    )->withAdditionalTransformation($valid_ip_constraint)
                ])
            ],
            (string) $url_builder->buildURI()
        )->withSubmitLabel($this->lng->txt('change'));
    }

    /**
     * @param array|string $selected_participants
     *
     * @return string
     */
    private function resolveInfoMessage(
        mixed $selected_participants,
    ): string {

        if ($selected_participants === 'ALL_OBJECTS') {
            return 'ip_range_for_all_participants';
        }
        if (count($selected_participants) === 0) {
            return $this->lng->txt('no_valid_participant_selection');
        }

        if (count($selected_participants) === 1) {
            return 'ip_range_for_single_participant';
        }

        return 'ip_range_for_selected_participants';
    }

    protected function allowActionForRecord(Participant $record): bool
    {
        return $record->isInvitedParticipant();
    }
}
