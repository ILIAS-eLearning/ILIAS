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

namespace ILIAS\UI\Implementation\Component\Entity;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        if ($component instanceof Entity) {
            return $this->renderEntity($component, $default_renderer);
        }
        $this->cannotHandleComponent($component);
    }

    protected function renderEntity(Entity $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate('tpl.entity.html', true, true);
        $secondary_identifier = $component->getSecondaryIdentifier();

        if (is_string($secondary_identifier)) {
            $tpl->touchBlock('secondid_string');
        } elseif ($secondary_identifier instanceof Component\Image\Image) {
            $tpl->touchBlock('secondid_image');
        } elseif ($secondary_identifier instanceof Component\Symbol\Symbol) {
            $tpl->touchBlock('secondid_symbol');
        } elseif ($secondary_identifier instanceof Component\Link\Link) {
            $tpl->touchBlock('secondid_link');
        } elseif ($secondary_identifier instanceof Component\Button\Shy) {
            $tpl->touchBlock('secondid_shy');
        }

        $tpl->setVariable('SECONDARY_IDENTIFIER', is_string($secondary_identifier) ? $secondary_identifier : $this->maybeRender($default_renderer, $secondary_identifier));

        $primary_identifier = $component->getPrimaryIdentifier();
        $primary_identifier = is_string($primary_identifier) ? $primary_identifier : $this->maybeRender($default_renderer, $primary_identifier);
        $tpl->setVariable('PRIMARY_IDENTIFIER', $primary_identifier);
        $tpl->setVariable('PRIMARY_IDENTIFIER_ID', $this->createId());

        $tpl->setVariable('BLOCKING_CONDITIONS', $this->maybeRender($default_renderer, ...$component->getBlockingAvailabilityConditions()));
        $tpl->setVariable('FEATURES', $this->maybeRender($default_renderer, ...$component->getFeaturedProperties()));
        $tpl->setVariable('PERSONAL_STATUS', $this->maybeRender($default_renderer, ...$component->getPersonalStatus()));
        $tpl->setVariable('MAIN_DETAILS', $this->maybeRender($default_renderer, ...$component->getMainDetails()));
        $tpl->setVariable('AVAILABILITY', $this->maybeRender($default_renderer, ...$component->getAvailability()));
        $tpl->setVariable('DETAILS', $this->maybeRender($default_renderer, ...$component->getDetails()));

        if (null !== $component->getWorkflow()) {
            $button_components = $this->createUnfinishedWorkflowActions($component->getWorkflow());
            $tpl->setVariable('WORKFLOW_ACTIONS', $default_renderer->render($button_components));
        }
        if ($actions = $component->getManagingActions()) {
            $actions_dropdown = $this->getUIFactory()->dropdown()->standard($actions);
            $tpl->setVariable('MANAGING_ACTIONS', $default_renderer->render($actions_dropdown));
        }
        if ($reactions = $component->getReactions()) {
            $tpl->setVariable('REACTIONS', $default_renderer->render($reactions));
        }
        if ($prio_reactions = $component->getPrioritizedReactions()) {
            $tpl->setVariable('PRIO_REACTIONS', $default_renderer->render($prio_reactions));
        }

        return $tpl->get();
    }

    protected function maybeRender(RendererInterface $default_renderer, Component\Component | null ...$values): ?string
    {
        //$values = array_filter($values);
        if ($values === []) {
            return null;
        }

        return $default_renderer->render($values);
    }

    /** @return Component\Button\Standard[] */
    protected function createUnfinishedWorkflowActions(Component\Listing\Workflow\Workflow $workflow): array
    {
        $actions = [];
        foreach ($workflow->getSteps() as $step) {
            if (null === $step->getAction() ||
                $step->getAvailability() !== Component\Listing\Workflow\Step::AVAILABLE ||
                ($step->getStatus() !== Component\Listing\Workflow\Step::NOT_STARTED &&
                $step->getStatus() !== Component\Listing\Workflow\Step::IN_PROGRESS)
            ) {
                continue;
            }
            $actions[] = $this->getUIFactory()->button()->standard($step->getLabel(), $step->getAction());
        }
        return $actions;
    }
}
