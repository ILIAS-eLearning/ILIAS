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

use ILIAS\Questions\AnswerForm\Capabilities\AdditionalFormStepAction;
use ILIAS\Questions\AnswerForm\Capabilities\Capability;
use ILIAS\Questions\AnswerForm\Properties as AnswerFormProperties;
use ILIAS\Questions\AnswerForm\Views\Edit as AnswerFormEditView;
use ILIAS\Questions\Presentation\Definitions\DefaultEnvironment;
use ILIAS\Questions\Presentation\Layout\EditForm;

class Edit
{
    private array $required_capabilites = [];
    private array $required_actions_with_tab = [];
    private array $required_form_step_actions = [];

    public function __construct(
        private readonly Factory $factory
    ) {
    }

    public function getRequiredCapabilities(): array
    {
        return $this->required_capabilites;
    }

    public function withRequiredCapabilities(
        array $capability_class_names
    ): self {
        $clone = clone $this;
        [
            'required_capabilities' => $clone->required_capabilites,
            'required_actions_with_tab' => $clone->required_actions_with_tab,
            'required_form_step_actions' => $clone->required_form_step_actions
        ] = $clone->buildRequiredCapabilitiesAndActions(
            $capability_class_names
        );
        return $clone;
    }

    public function onAnswerFormUpdate(
        AnswerFormProperties $properties
    ): void {
        foreach ($this->required_capabilites as $capability) {
            $capability->onAnswerFormUpdate($properties);
        }
    }

    public function edit(
        \ilTabsGUI $tabs_gui,
        DefaultEnvironment $environment,
        AnswerFormEditView $edit_view,
        string $action_from_get
    ): EditForm|AnswerFormProperties|null {
        $environment->setEditAnswerFormTabs(
            $this->required_actions_with_tab
        );

        $action = $this->required_actions_with_tab[$action_from_get] ?? null;
        if ($action !== null) {
            $action->activateTab($tabs_gui);
        } else {
            $environment->setEditAnswerFormBackTarget();
            $action = $this->retrieveNextFormStepFromActionIdentifier(
                $edit_view,
                $action_from_get
            );
        }

        if ($action === null) {
            return null;
        }

        return $action->do(
            $environment->withActionParameter($action_from_get)
        );
    }

    public function providesAnswerFormEditAdditionalSteps(): bool
    {
        return array_filter(
            $this->required_capabilites,
            fn(Capability $v): bool => $v->providesAnswerFormEditAdditionalStep()
        ) !== [];
    }

    public function doFirstFormStepAction(
        DefaultEnvironment $environment,
        AnswerFormEditView $edit_view
    ): EditForm|AnswerFormProperties {
        if ($this->required_form_step_actions === []) {
            return $environment->getAnswerFormProperties();
        }

        $keys = array_keys($this->required_form_step_actions);

        return $this->required_form_step_actions[$keys[0]]
            ->withPrevious(
                $edit_view
            )->withNext(
                isset($keys[1])
                    ? $this->required_form_step_actions[$keys[1]]
                    : null
            )->do(
                $environment->withActionParameter($keys[0])
            );
    }

    private function retrieveNextFormStepFromActionIdentifier(
        AnswerFormEditView $edit_view,
        string $action_identifier
    ): ?AdditionalFormStepAction {
        $action = $this->required_form_step_actions[$action_identifier] ?? null;
        if ($action === null) {
            return null;
        }

        $keys = array_keys($this->required_form_step_actions);
        $current_index = array_search($action_identifier, $keys);

        $next_index = $current_index + 1;
        if (isset($keys[$next_index])) {
            $action = $action->withNext(
                $this->required_form_step_actions[$next_index]
            );
        }

        return $action->withPrevious(
            $current_index > 0
                ? $this->required_form_step_actions[$keys[$current_index - 1]]
                : $edit_view
        );
    }

    /**
     * @param list<string> $capabilities
     * @return list<\ILIAS\Questions\AnswerForm\Capabilities\Capability>
     */
    private function buildRequiredCapabilitiesAndActions(
        array $capabilities
    ): array {
        return array_reduce(
            $capabilities,
            function (array $c, string $v): array {
                $capability = $this->factory->get($v);
                if ($capability === null) {
                    throw new \InvalidArgumentException(
                        "The capability {$v} does not exist."
                    );
                }
                $c['required_capabilities'][] = $capability;

                $action_with_tab = $capability->getAnswerFormEditAdditionalTab();
                if ($action_with_tab !== null) {
                    $c['required_actions_with_tab'][$action_with_tab->getIdentifier()] = $action_with_tab;
                }

                $form_step_action = $capability->getAnswerFormEditAdditionalStep();
                if ($form_step_action !== null) {
                    $c['required_form_step_actions'][$form_step_action->getIdentifier()] = $form_step_action;
                }

                return $c;
            },
            [
                'required_capabilities' => [],
                'required_actions_with_tab' => [],
                'required_form_step_actions' => []
            ]
        );
    }
}
