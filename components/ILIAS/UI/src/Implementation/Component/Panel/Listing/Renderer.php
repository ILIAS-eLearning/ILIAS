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
use ILIAS\UI\Implementation\Render\ResourceRegistry;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    public function render(C\Component $component, RendererInterface $default_renderer): string
    {
        $this->checkComponent($component);

        if ($component instanceof C\Panel\Listing\Standard) {
            return $this->renderStandard($component, $default_renderer);
        }
        return '';
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
        $f = $this->getUIFactory();
        $title = $component->getTitle();
        $actions = $component->getActions();
        $view_controls = $component->getViewControls();

        if ($title !== "" || $actions || $view_controls) {
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
            if ($component->isExpandable()) {
                $tpl->touchBlock("panel_expandable");
                $tpl->touchBlock("body_expandable");

                $component = $component->withAdditionalOnLoadCode(
                    function ($id) {
                        return "il.UI.panel.initExpandable();";
                    }
                );
                $id = $this->bindJavaScript($component);
                if ($id === null) {
                    $id = $this->createId();
                }
                $tpl->setVariable("BODY_ID", $id . "_body");

                $collapse_action = $component->getCollapseAction();
                $opener_collapse = $f->button()->bulky($f->symbol()->glyph()->collapse(), $component->getTitle(), "")
                                     ->withAdditionalOnLoadCode(fn ($id) => "$('#$id').on('click', function(event) {
					il.UI.panel.onCollapseCmd(event, '$id', '$collapse_action');
					event.preventDefault();
			    });");
                $tpl->setVariable("COLLAPSE_BUTTON", $default_renderer->render($opener_collapse));

                $expand_action = $component->getExpandAction();
                $opener_expand = $f->button()->bulky($f->symbol()->glyph()->expand(), $component->getTitle(), "")
                                   ->withAdditionalOnLoadCode(fn ($id) => "$('#$id').on('click', function(event) {
					il.UI.panel.onExpandCmd(event, '$id', '$expand_action');
					event.preventDefault();
			    });");
                $tpl->setVariable("EXPAND_BUTTON", $default_renderer->render($opener_expand));

                if ($component->isExpanded()) {
                    $tpl->setVariable("BODY_EXPANDED", "in");
                }
            } else {
                $tpl->setVariable("TITLE", $title);
            }
        }
        return $tpl;
    }

    public function registerResources (ResourceRegistry $registry): void
    {
        parent::registerResources($registry);
        $registry->register('./src/UI/templates/js/Panel/panel.js');
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName(): array
    {
        return array(C\Panel\Listing\Standard::class);
    }
}
