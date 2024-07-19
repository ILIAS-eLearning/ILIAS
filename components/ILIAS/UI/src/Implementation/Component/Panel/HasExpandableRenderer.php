<?php

declare(strict_types=1);

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

namespace ILIAS\UI\Implementation\Component\Panel;

use ILIAS\Data\URI;
use ILIAS\UI\Factory;
use ILIAS\UI\Component as C;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Implementation\Render\Template as Template;

trait HasExpandableRenderer
{
    protected function declareExpandable(
        C\Panel\IsExpandable $component,
        Template $tpl
    ): Template {
        if ($component->isExpandable()) {
            $tpl->touchBlock("panel_expandable");
            $tpl->setCurrentBlock("body_expandable");
            $component->isExpanded()
                ? $tpl->setVariable("BODY_EXPANDED", 1)
                : $tpl->setVariable("BODY_EXPANDED", 0);
            $tpl->parseCurrentBlock();
        }

        return $tpl;
    }

    protected function parseExpandingHeader(
        C\Panel\IsExpandable $component,
        RendererInterface $default_renderer,
        Factory $factory
    ): Template {
        $tpl = $this->getTemplate("tpl.heading_expanding.html", true, true);
        $f = $factory;
        $title = $component->getTitle();
        $actions = $component->getActions();
        $view_controls = $component->getViewControls();

        if ($title !== "" || $actions || $view_controls) {
            if ($view_controls) {
                if ($component->isExpandable()) {
                    $tpl->setCurrentBlock("vc_expandable");
                    $component->isExpanded()
                        ? $tpl->setVariable("VC_EXPANDED", 1)
                        : $tpl->setVariable("VC_EXPANDED", 0);
                    $tpl->parseCurrentBlock();
                }
                foreach ($view_controls as $view_control) {
                    $tpl->setCurrentBlock("view_controls");
                    $tpl->setVariable("VIEW_CONTROL", $default_renderer->render($view_control));
                    $tpl->parseCurrentBlock();
                }
            }

            if ($actions) {
                $tpl->setVariable("ACTIONS", $default_renderer->render($actions));
            }

            if ($component->isExpandable()) {
                $component_type = "standard";
                if ($component instanceof C\Panel\Listing\Listing) {
                    $component_type = "listing";
                }

                $tpl->setCurrentBlock("toggler");
                $toggler_collapse = $f->button()->bulky($f->symbol()->glyph()->collapse(), $title, "");
                $collapse_action = $component->getCollapseAction();
                if ($collapse_action instanceof URI) {
                    $toggler_collapse = $toggler_collapse->withAdditionalOnLoadCode(
                        fn($id) => "document.getElementById('$id').addEventListener('click', (event) => {
					    il.UI.panel.onCollapseCmdAction(event, '$id', '$component_type', '$collapse_action');
					});"
                    );
                } elseif ($collapse_action instanceof C\Signal) {
                    $collapse_signal = [
                        "signal_id" => $collapse_action->getId(),
                        "event" => "click",
                        "options" => $collapse_action->getOptions()
                    ];
                    $collapse_signal = json_encode($collapse_signal);
                    $toggler_collapse = $toggler_collapse->withAdditionalOnLoadCode(
                        fn($id) => "document.getElementById('$id').addEventListener('click', (event) => {
					    il.UI.panel.onCollapseCmdSignal(event, '$id', '$component_type', $collapse_signal);
					});"
                    );
                }
                $tpl->setVariable("COLLAPSE_BUTTON", $default_renderer->render($toggler_collapse));
                $component->isExpanded()
                    ? $tpl->setVariable("COLLAPSE_BUTTON_VISIBLE", 1)
                    : $tpl->setVariable("COLLAPSE_BUTTON_VISIBLE", 0);

                $toggler_expand = $f->button()->bulky($f->symbol()->glyph()->expand(), $title, "");
                $expand_action = $component->getExpandAction();
                if ($expand_action instanceof URI) {
                    $toggler_expand = $toggler_expand->withAdditionalOnLoadCode(
                        fn($id) => "document.getElementById('$id').addEventListener('click', (event) => {
                        il.UI.panel.onExpandCmdAction(event, '$id', '$component_type', '$expand_action');
                    });"
                    );
                } elseif ($expand_action instanceof C\Signal) {
                    $expand_signal = [
                        "signal_id" => $expand_action->getId(),
                        "event" => "click",
                        "options" => $expand_action->getOptions()
                    ];
                    $expand_signal = json_encode($expand_signal);
                    $toggler_expand = $toggler_expand->withAdditionalOnLoadCode(
                        fn($id) => "document.getElementById('$id').addEventListener('click', (event) => {
					    il.UI.panel.onExpandCmdSignal(event, '$id', '$component_type', $expand_signal);
					});"
                    );
                }
                $tpl->setVariable("EXPAND_BUTTON", $default_renderer->render($toggler_expand));
                $component->isExpanded()
                    ? $tpl->setVariable("EXPAND_BUTTON_VISIBLE", 0)
                    : $tpl->setVariable("EXPAND_BUTTON_VISIBLE", 1);
                $tpl->parseCurrentBlock();
            } else {
                $tpl->setVariable("TITLE", $title);
            }
            $tpl->setCurrentBlock("heading");
            $tpl->parseCurrentBlock();
        }

        return $tpl;
    }
}
