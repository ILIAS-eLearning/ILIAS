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
use ILIAS\Refinery\Factory as Refinery;

class ParticipantTableIpRangeAction implements TableAction
{
    public const ACTION_ID = 'client_ip_range';

    public function __construct(
        private readonly Language $lng,
        private readonly \ilGlobalTemplateInterface $tpl,
        private readonly UIFactory $ui_factory,
        private readonly Refinery $refinery,
        private readonly ParticipantRepository $participant_repository,
        private readonly \ilTestAccess $test_access,
    ) {
    }

    public function getActionId(): string
    {
        return self::ACTION_ID;
    }

    public function isAvailable(): bool
    {
        return $this->test_access->checkManageParticipantsAccess();
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
        $valid_ip_constraint = $this->refinery->custom()->constraint(
            fn(?string $ip): bool => $ip === null
                || $ip === ''
                || filter_var($ip, FILTER_VALIDATE_IP) !== false,
            $this->lng->txt('invalid_ip')
        );
        $validate_order = $this->refinery->custom()->constraint(
            function (?array $vs): bool {
                if ($vs === null) {
                    return true;
                }
                return $this->checkIpRangeValidity(
                    $vs['from'],
                    $vs['to']
                );
            },
            sprintf($this->lng->txt('not_greater_than'), $this->lng->txt('max_ip_label'), $this->lng->txt('min_ip_label'))
        );
        $ip_range_group_trafo = $this->refinery->custom()->transformation(
            static function (?array $vs): array {
                if ($vs === null) {
                    $vs = [
                        'from' => null,
                        'to' => null
                    ];
                }
                return $vs;
            }
        );


        $participant_rows = array_map(
            fn(Participant $participant) => sprintf(
                '%s, %s',
                $participant->getLastname(),
                $participant->getFirstname()
            ),
            $selected_participants
        );

        return $this->ui_factory->modal()->roundtrip(
            $this->lng->txt('client_ip_range'),
            [
                $this->ui_factory->messageBox()->info(
                    $this->lng->txt(
                        $this->resolveInfoMessage(
                            $selected_participants,
                            $all_participants_selected
                        )
                    )
                ),
                $this->ui_factory->listing()->unordered($participant_rows)
            ],
            [
                'ip_range' => $this->ui_factory->input()->field()->group([
                    'from' => $this->ui_factory->input()->field()->text(
                        $this->lng->txt('min_ip_label')
                    )->withAdditionalTransformation($valid_ip_constraint),
                    'to' => $this->ui_factory->input()->field()->text(
                        $this->lng->txt('max_ip_label'),
                        $this->lng->txt('ip_range_byline')
                    )->withAdditionalTransformation($valid_ip_constraint),
                ])->withValue(
                    $this->isUniqueClientIp($selected_participants)
                        ? [
                            'from' => $selected_participants[0]->getClientIpFrom() ?? '',
                            'to' => $selected_participants[0]->getClientIpTo() ?? ''
                        ]
                        : [
                            'from' => '',
                            'to' => ''
                        ]
                )
                    ->withAdditionalTransformation($ip_range_group_trafo)
                    ->withAdditionalTransformation($validate_order)
            ],
            $url_builder->buildURI()->__toString()
        )->withSubmitLabel($this->lng->txt('change'));
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

        $this->participant_repository->updateIpRange(
            array_map(
                static fn(Participant $v) => $v->withClientIpFrom($data['ip_range']['from'])
                    ->withClientIpTo($data['ip_range']['to']),
                $selected_participants
            )
        );

        $this->tpl->setOnScreenMessage(
            \ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS,
            $this->lng->txt('ip_range_updated'),
            true
        );
        return null;
    }

    public function allowActionForRecord(Participant $record): bool
    {
        return $record->isInvitedParticipant();
    }

    /**
     * @param array $selected_participants
     */
    private function resolveInfoMessage(
        array $selected_participants,
        bool $all_participants_selected
    ): string {
        if ($all_participants_selected) {
            return 'ip_range_for_all_participants';
        }

        if (count($selected_participants) === 1) {
            return 'ip_range_for_single_participant';
        }

        return 'ip_range_for_selected_participants';
    }

    private function isUniqueClientIp(array $selected_participants): bool
    {
        return count($selected_participants) === 1
            || count(array_unique(array_map(
                fn(Participant $participant) => $participant->getClientIpFrom() . '-' . $participant->getClientIpTo(),
                $selected_participants
            ))) === 1;
    }

    private function checkIpRangeValidity(string $start, string $end): bool
    {
        if (filter_var($start, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false
            && filter_var($end, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
            return ip2long($start) <= ip2long($end);
        }

        if (filter_var($start, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false
            && filter_var($end, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
            return bin2hex(inet_pton($start)) <= bin2hex(inet_pton($end));
        }
        return false;
    }

    public function getSelectionErrorMessage(): ?string
    {
        return null;
    }
}
