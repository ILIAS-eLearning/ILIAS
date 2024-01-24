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

namespace ILIAS\UI\Implementation\Component\Listing\Entity;

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
    protected function renderComponent(Component\Component $component, RendererInterface $default_renderer): ?string
    {
        if ($component instanceof EntityListing) {
            return $this->renderEntityListing($component, $default_renderer);
        }

        return null;
    }

    protected function renderEntityListing(EntityListing $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate('tpl.entitylisting.html', true, true);

        foreach ($component->getEntities(
            $this->getUIFactory()
        ) as $entity) {
            $tpl->setCurrentBlock('entry');
            $tpl->setVariable('ENTITY', $default_renderer->render($entity));
            $tpl->parseCurrentBlock();
        }
        return $tpl->get();
    }
}
