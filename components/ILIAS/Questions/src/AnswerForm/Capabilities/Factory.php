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

namespace ILIAS\Questions\AnswerForm\Capabilities;

use ILIAS\Questions\AnswerForm\Capabilities\Capability;
use ILIAS\Questions\AnswerForm\Capabilities\Definitions\AdditionalTabProvider;
use ILIAS\Questions\AnswerForm\Capabilities\Definitions\AdditionalStepProvider;
use ILIAS\Questions\AnswerForm\Capabilities\Definitions\FeedbackProvider;
use ILIAS\Questions\AnswerForm\Capabilities\Definitions\MarkingProvider;
use ILIAS\Questions\AnswerForm\Capabilities\ParticipantViewProvider;

class Factory
{
    /**
     * @param array<string, Capability> $available_capabilities
     */
    private readonly array $available_capabilities;

    /**
     * @param list<Capability> $available_capabilities
     */
    public function __construct(
        array $available_capabilities
    ) {
        $this->available_capabilities = array_reduce(
            $available_capabilities,
            function (array $c, Capability $v): array {
                $c[$v->getIdentifier()] = $v;
                return $c;
            },
            []
        );
    }

    /**
     *
     * @param list<class-string<Capability>> $capability_identifiers
     */
    public function get(
        array $capability_identifiers
    ): RequiredCapabilities {
        $required_capabilities = [];
        $participant_view_providers = [];
        $marking_providers = [];
        $required_feedback_providers = [];
        $required_actions_with_tab = [];
        $required_form_step_actions = [];

        foreach ($capability_identifiers as $capability_identifier) {
            if (!isset($this->available_capabilities[$capability_identifier])) {
                throw new \InvalidArgumentException(
                    "The capability {$capability_identifier} does not exist."
                );
            }

            $capability = $this->available_capabilities[$capability_identifier];
            $required_capabilities[$capability_identifier] = $capability;

            if ($capability instanceof ParticipantViewProvider) {
                $participant_view_providers[] = $capability;
            }

            if ($capability instanceof MarkingProvider) {
                $marking_providers[] = $capability;
            }

            if ($capability instanceof FeedbackProvider) {
                $required_feedback_providers[] = $capability;
            }

            if ($capability instanceof AdditionalTabProvider) {
                $action_with_tab = $capability->getAnswerFormEditAdditionalTab();
                $required_actions_with_tab[$action_with_tab->getIdentifier()] = $action_with_tab;
            }

            if ($capability instanceof AdditionalStepProvider) {
                $form_step_action = $capability->getAnswerFormEditAdditionalStep();
                $required_form_step_actions[$form_step_action->getIdentifier()] = $form_step_action;
            }
        }

        if (count($participant_view_providers) !== 1 || count($marking_providers) > 1) {
            throw new \InvalidArgumentException(
                'You have to provide exactly one Capability providing a Participant View'
                . 'and one Capability providing Marking at most.'
            );
        }

        return new RequiredCapabilities(
            $required_capabilities,
            $participant_view_providers[0],
            $required_feedback_providers,
            $required_actions_with_tab,
            $required_form_step_actions,
            $marking_providers[0] ?? null
        );
    }
}
