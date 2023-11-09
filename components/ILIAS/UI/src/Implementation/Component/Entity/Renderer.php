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

//use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Implementation\Render\Template;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        $this->checkComponent($component);
        return $this->renderEntity($component, $default_renderer);
    }

    protected function renderEntity(Entity $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate('tpl.entity.html', true, true);
        $secondary_identifier = $component->getSecondaryIdentifier();

        if (is_string($secondary_identifier)) {
            $tpl->touchBlock('secondid_string');
        } elseif ($secondary_identifier instanceof Component\Image\Image) {
            $tpl->touchBlock('secondid_image');
        } elseif ($secondary_identifier instanceof Component\Image\Symbol) {
            $tpl->touchBlock('secondid_symbol');
        } elseif ($secondary_identifier instanceof Component\Image\Link) {
            $tpl->touchBlock('secondid_link');
        } elseif ($secondary_identifier instanceof Component\Image\Shy) {
            $tpl->touchBlock('secondid_shy');
        }

        $tpl->setVariable('SECONDARY_IDENTIFIER', is_string($secondary_identifier) ? $secondary_identifier : $this->maybeRender($default_renderer, $secondary_identifier));

        $primary_identifier = $component->getPrimaryIdentifier();
        $primary_identifier = is_string($primary_identifier) ? $primary_identifier : $this->maybeRender($default_renderer, $primary_identifier);
        $tpl->setVariable('PRIMARY_IDENTIFIER', $primary_identifier);

        $tpl->setVariable('BLOCKING_CONDITIONS', $this->maybeRender($default_renderer, ...$component->getBlockingAvailabilityConditions()));
        $tpl->setVariable('FEATURES', $this->maybeRender($default_renderer, ...$component->getFeaturedProperties()));
        $tpl->setVariable('PERSONAL_STATUS', $this->maybeRender($default_renderer, ...$component->getPersonalStatus()));
        $tpl->setVariable('MAIN_DETAILS', $this->maybeRender($default_renderer, ...$component->getMainDetails()));
        $tpl->setVariable('AVAILABILITY', $this->maybeRender($default_renderer, ...$component->getAvailability()));
        $tpl->setVariable('DETAILS', $this->maybeRender($default_renderer, ...$component->getDetails()));

        if ($actions = $component->getActions()) {
            $actions_dropdown = $this->getUIFactory()->dropdown()->standard($actions);
            $tpl->setVariable('ACTIONS', $default_renderer->render($actions_dropdown));
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

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName(): array
    {
        return [
            Component\Entity\Standard::class
        ];
    }
}
