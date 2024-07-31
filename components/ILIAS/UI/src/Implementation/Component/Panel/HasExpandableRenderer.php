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
        string $component_id,
        Template $tpl
    ): Template {
        $tpl->touchBlock("panel_expandable");
        $tpl->setCurrentBlock("body_expandable");
        $tpl->setVariable("PANEL_ID", $component_id);
        $component->isExpanded()
            ? $tpl->setVariable("BODY_EXPANDED", 1)
            : $tpl->setVariable("BODY_EXPANDED", 0);
        $tpl->parseCurrentBlock();

        return $tpl;
    }

    protected function parseHeader(
        C\Panel\IsExpandable $component,
        string $component_id,
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
                $tpl->setCurrentBlock("toggler");
                $tpl->setVariable("TITLE_TOGGLER", $title);
                $glyph_collapse = $f->symbol()->glyph()->collapse();
                $tpl->setVariable("COLLAPSE_GLYPH", $default_renderer->render($glyph_collapse));
                $glyph_expand = $f->symbol()->glyph()->expand();
                $tpl->setVariable("EXPAND_GLYPH", $default_renderer->render($glyph_expand));

                if ($component->isExpanded()) {
                    $tpl->setVariable("ARIA_EXPANDED", "true");
                    $tpl->setVariable("PANEL_ID", $component_id);
                    $tpl->setVariable("COLLAPSE_GLYPH_VISIBLE", 1);
                    $tpl->setVariable("EXPAND_GLYPH_VISIBLE", 0);
                } else {
                    $tpl->setVariable("ARIA_EXPANDED", "false");
                    $tpl->setVariable("PANEL_ID", $component_id);
                    $tpl->setVariable("COLLAPSE_GLYPH_VISIBLE", 0);
                    $tpl->setVariable("EXPAND_GLYPH_VISIBLE", 1);
                }
                $tpl->parseCurrentBlock();
            } else {
                $tpl->setVariable("TITLE", $title);
            }
            $tpl->setCurrentBlock("heading");
            $tpl->parseCurrentBlock();
        }

        return $tpl;
    }

    protected function parseActions(
        C\Panel\IsExpandable $component
    ): C\Panel\IsExpandable {
        $component_type = "standard";
        if ($component instanceof C\Panel\Listing\Listing) {
            $component_type = "listing";
        }

        $collapse_action = $component->getCollapseAction();
        $collapse_action_uri = "";
        $collapse_action_signal = false;
        if ($collapse_action instanceof URI) {
            $collapse_action_uri = $collapse_action;
        } elseif ($collapse_action instanceof C\Signal) {
            $collapse_action_signal = [
                "signal_id" => $collapse_action->getId(),
                "event" => "click",
                "options" => $collapse_action->getOptions()
            ];
        }

        $expand_action = $component->getExpandAction();
        $expand_action_uri = "";
        $expand_action_signal = false;
        if ($expand_action instanceof URI) {
            $expand_action_uri = $expand_action;
        } elseif ($expand_action instanceof C\Signal) {
            $expand_action_signal = [
                "signal_id" => $expand_action->getId(),
                "event" => "click",
                "options" => $expand_action->getOptions()
            ];
        }

        $collapse_action_signal = json_encode($collapse_action_signal);
        $expand_action_signal = json_encode($expand_action_signal);
        $component = $component->withAdditionalOnLoadCode(
            function ($id) use (
                $component_type,
                $collapse_action_uri,
                $expand_action_uri,
                $collapse_action_signal,
                $expand_action_signal
            ) {
                return "il.UI.panel.initExpandable(
                    '$id',
                    '$component_type',
                    '$collapse_action_uri',
                    '$expand_action_uri',
                    $collapse_action_signal,
                    $expand_action_signal
                );";
            }
        );

        return $component;
    }
}
