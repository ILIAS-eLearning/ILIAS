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

namespace ILIAS\UI\Implementation\Component\Panel\Listing;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Item\Group;
use ILIAS\UI\Implementation\Render\Template as Template;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    protected function renderComponent(C\Component $component, RendererInterface $default_renderer): ?string
    {
        if ($component instanceof C\Panel\Listing\Standard) {
            return $this->renderStandard($component, $default_renderer);
        }
        return null;
    }

    protected function renderStandard(C\Panel\Listing\Listing $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.listing_standard.html", true, true);

        $tpl = $this->parseHeader($component, $default_renderer, $tpl);

        foreach ($component->getItemGroups() as $group) {
            if ($group instanceof Group) {
                $tpl->setCurrentBlock("group");
                $tpl->setVariable("ITEM_GROUP", $default_renderer->render($group));
                $tpl->parseCurrentBlock();
            }
        }

        return $tpl->get();
    }

    protected function parseHeader(
        C\Panel\Listing\Standard $component,
        RendererInterface $default_renderer,
        Template $tpl
    ): Template {
        $title = $component->getTitle();
        $actions = $component->getActions();
        $view_controls = $component->getViewControls();

        if ($title !== "" || $actions || $view_controls) {
            $tpl->setVariable("TITLE", $title);
            if ($actions) {
                $tpl->setVariable("ACTIONS", $default_renderer->render($actions));
            }
            if ($view_controls) {
                foreach ($view_controls as $view_control) {
                    $tpl->setCurrentBlock("view_controls");
                    $tpl->setVariable("VIEW_CONTROL", $default_renderer->render($view_control));
                    $tpl->parseCurrentBlock();
                }
            }
            $tpl->setCurrentBlock("heading");
            $tpl->parseCurrentBlock();
        }
        return $tpl;
    }
}
