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
use ILIAS\Questions\AnswerForm\Capabilities\Definitions\ActionWithTab;
use ILIAS\Questions\AnswerForm\Capabilities\Definitions\AdditionalFormStepAction;
use ILIAS\Questions\AnswerForm\Capabilities\Definitions\Marking;
use ILIAS\Questions\AnswerForm\Capabilities\Definitions\MarkingProvider;
use ILIAS\Questions\AnswerForm\Capabilities\Definitions\PageMigrationProvider;
use ILIAS\Questions\AnswerForm\Capabilities\ParticipantViewProvider;
use ILIAS\Questions\AnswerForm\Properties as AnswerFormProperties;
use ILIAS\Questions\AnswerForm\Views\Edit as AnswerFormEditView;
use ILIAS\Questions\Presentation\Definitions\DefaultEnvironment;
use ILIAS\Questions\Presentation\Layout\Async;
use ILIAS\Questions\Presentation\Layout\EditForm;
use ILIAS\Questions\Presentation\Layout\Viewable;

class RequiredCapabilities
{
    /**
     * @param array<string, Capability> $capability_class_names
     * @param array<string, Viewable> $required_feedback_views
     * @param array<string, ActionWithTab> $required_actions_with_tab
     * @param array<string, AdditionalStepAction> $required_step_actions
     * @param array<string, PageMigrationProvider> $required_step_actions
     */
    public function __construct(
        private readonly array $capabilities,
        private ParticipantViewProvider $participant_view_provider,
        private array $required_feedback_providers,
        private array $required_actions_with_tab,
        private array $required_form_step_actions,
        private array $required_page_migration_providers,
        private ?MarkingProvider $marking_provider
    ) {

    }

    /**
     * @param list<AnswerFormProperties> $answer_form_properties
     */
    public function areAllCapabilitiesSupportedByAnswerForms(
        array $answer_form_properties
    ): bool {
        foreach ($answer_form_properties as $property) {
            foreach ($this->capabilities as $capability) {
                if (!$capability->isAvailableFor($property)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function getParticipantViewProvider(): ParticipantViewProvider
    {
        return $this->participant_view_provider;
    }

    /**
     * @return array<string, Viewable>
     */
    public function getRequiredFeedbackProviders(): array
    {
        return $this->required_feedback_providers;
    }

    /**
     * @return array<string, AdditionalStepAction>
     */
    public function getRequiredFormStepActions(): array
    {
        return $this->required_form_step_actions;
    }

    /**
     * @return array<string, PageMigrationProviders>
     */
    public function getRequiredPageMigrationProviders(): array
    {
        return $this->required_page_migration_providers;
    }

    public function isMarkingRequired(): bool
    {
        return $this->marking_provider !== null;
    }

    public function getMarking(
        AnswerFormProperties $answer_form_properties
    ): ?Marking {
        return $this->marking_provider?->getMarking($answer_form_properties);
    }

    public function isCapabilityRequired(
        string $identifier
    ): bool {
        return array_key_exists($identifier, $this->capabilities);
    }

    public function onAnswerFormUpdate(
        AnswerFormProperties $properties
    ): void {
        foreach ($this->capabilities as $capability) {
            $capability->onAnswerFormUpdate($properties);
        }
    }

    public function edit(
        \ilTabsGUI $tabs_gui,
        DefaultEnvironment $environment,
        AnswerFormEditView $edit_view,
        string $action_from_get
    ): Async|Viewable|AnswerFormProperties|null {
        $environment->setEditAnswerFormTabs(
            $this->required_actions_with_tab
        );

        $tab_action = $this->required_actions_with_tab[$action_from_get] ?? null;
        if ($tab_action !== null) {
            $tab_action->activateTab($tabs_gui);

            return $tab_action->do(
                $environment->withActionParameter($action_from_get)
            );
        }

        $step_action = $this->retrieveNextFormStepFromActionIdentifier(
            $edit_view,
            $action_from_get
        );

        if ($step_action === null) {
            return null;
        }

        $environment->setEditAnswerFormBackTarget();
        return $step_action->do(
            $environment->withActionParameter($action_from_get)
        );
    }

    public function additionalAnswerFormStepsRequired(): bool
    {
        return $this->required_form_step_actions !== [];
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
}
