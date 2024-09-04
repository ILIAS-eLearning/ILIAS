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

use ilCtrlInterface;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\HTTP\Services as HttpService;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Test\Logging\TestLogger;
use ILIAS\UI\Component\Input\Container\Form\Standard;
use ILIAS\UI\Component\Modal\Modal;
use ILIAS\UI\Component\Modal\RoundTrip;
use ILIAS\UI\Component\Table\DataRow;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Implementation\Component\Table\AbstractTable;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ilLanguage;
use ilTestParticipantsGUI;

use function array_map;
use function count;
use function dump;
use function ILIAS\UI\examples\Symbol\Glyph\Filter\filter;
use function is_array;
use function sprintf;

class ParticipantTableIpRangeAction extends ParticipantTableModalAction
{
    public const ACTION_ID = 'client_ip_range';

    public function getActionId(): string
    {
        return self::ACTION_ID;
    }

    /**
     * @param Participant $record
     */
    public function onDataRow(DataRow $row, mixed $record): DataRow
    {
        if (!$record->isInvitedParticipant()) {
            return $row->withDisabledAction($this->getActionId());
        }

        return $row;
    }

    protected function onSubmit(Standard|Modal $modal, array|string $selected_participants): void
    {
        $data = $modal->getData();

        $participants = $this->resolveSelectedParticipants($selected_participants);

        $this->repository->updateIpRange($participants, $data['ip_range']);
    }

    protected function getModal(URLBuilder $url_builder, array|string $selected_participants): Modal|Standard
    {
        $valid_ip_constraint = $this->refinery->custom()->constraint(
            fn(?string $ip): bool => $ip === null || filter_var($ip, FILTER_VALIDATE_IP) !== false,
            $this->lng->txt('invalid_ip')
        );
        $participants = is_array($selected_participants) ? $this->resolveSelectedParticipants($selected_participants) : [];

        /** @var RoundTrip|Standard $modal */
        return $this->ui_factory->modal()->roundtrip(
            $this->lng->txt('client_ip_range'),
            [
                $this->ui_factory->messageBox()->info(
                    $this->lng->txt($this->resolveInfoMessage($selected_participants))
                ),
                ...array_map(
                    fn(\ilTestParticipant $participant) => $this->ui_factory->legacy(
                        sprintf(
                            '<p>%s, %s</p>',
                            $participant->getLastname(),
                            $participant->getFirstname()
                        )
                    ),
                    $participants
                )
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
        if (count($selected_participants) === 1) {
            return 'ip_range_for_single_participant';
        }

        return 'ip_range_for_selected_participants';
    }

}
