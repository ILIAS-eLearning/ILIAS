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

use ILIAS\UI\Component;
use ILIAS\UI\Renderer as RendererInterface;

/**
 * Compact entity rendering inside an Entity Input (form context).
 */
class EntityFieldContextRenderer extends Renderer
{
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        if ($component instanceof Entity && $this->isCompactListItem($component)) {
            return $this->renderCompactEntity($component, $default_renderer);
        }

        return parent::render($component, $default_renderer);
    }

    protected function renderCompactEntity(
        Component\Entity\Entity $component,
        RendererInterface $default_renderer
    ): string {
        $tpl = $this->getTemplate('tpl.entity-brief.html', true, true);

        $primary_identifier = $component->getPrimaryIdentifier();
        $content = is_string($primary_identifier)
            ? $primary_identifier
            : $default_renderer->render($primary_identifier);

        $tpl->setVariable('CONTENT', $content);

        return $tpl->get();
    }

    protected function isCompactListItem(Entity $component): bool
    {
        return $component->getBlockingAvailabilityConditions() === [] &&
            $component->getFeaturedProperties() === [] &&
            $component->getMainDetails() === [] &&
            $component->getDetails() === [] &&
            $component->getAvailability() === [] &&
            $component->getPersonalStatus() === [] &&
            $component->getPrioritizedReactions() === [] &&
            $component->getReactions() === [] &&
            $component->getManagingActions() === [] &&
            $component->getWorkflow() === null &&
            $component->getSecondaryIdentifier() === '';
    }
}
