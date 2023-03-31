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
        $tpl->setVariable('BLOCKING_CONDITIONS', $this->maybeRender($component->getBlockingAvailabilityConditions(), $default_renderer));
        $tpl->setVariable('SECONDARY_IDENTIFIER', $this->maybeRender($component->getSecondaryIdentifier(), $default_renderer));
        $tpl->setVariable('FEATURES', $this->maybeRender($component->getFeaturedProperties(), $default_renderer));
        $tpl->setVariable('PRIMARY_IDENTIFIER', $this->maybeRender($component->getPrimaryIdentifier(), $default_renderer));
        $tpl->setVariable('PERSONAL_STATUS', $this->maybeRender($component->getPersonalStatus(), $default_renderer));
        $tpl->setVariable('MAIN_DETAILS', $this->maybeRender($component->getMainDetails(), $default_renderer));
        $tpl->setVariable('AVAILABILITY', $this->maybeRender($component->getAvailability(), $default_renderer));
        $tpl->setVariable('DETAILS', $this->maybeRender($component->getDetails(), $default_renderer));

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

    protected function maybeRender(Component\Component|array|string|null $value, RendererInterface $default_renderer): ?string
    {
        if (is_null($value) || is_string($value)) {
            return $value;
        }
        return $default_renderer->render($value);
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
