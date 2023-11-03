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

namespace ILIAS\UI\Implementation\Render;

use ILIAS\UI\HelpTextRetriever;
use ILIAS\UI\Help;

/**
 * This class is supposed to unify rendering of tooltips over all components
 * and should also be usable by legacy UI components.
 */
class TooltipRenderer
{
    public function __construct(
        protected HelpTextRetriever $help_text_retriever,
        protected $get_template
    ) {
        if (!is_callable($this->get_template)) {
            throw new \InvalidArgumentException("\$get_template should be callable.");
        }
    }

    /**
     * This will provide functions that can be used to embed a components html
     * into some html required for the tooltip, if there are in fact any tooltips
     * for the given help topics. The first resulting function takes an id to be used
     * as the tooltips id and the html of the component. The second resulting function
     * takes the id of the component and creates an appropriate javascript to bind
     * the required javascript to the component.
     *
     * If there are no tooltips for the help topic, this will return nothing.
     *
     * @return ?((string, string) -> string, string -> string)
     */
    public function maybeGetTooltipEmbedding(Help\Topic ...$topics): ?array
    {
        if (count($topics) === 0) {
            return null;
        }

        $tooltips = $this->help_text_retriever->getHelpText(Help\Purpose::Tooltip(), ...$topics);
        if (count($tooltips) === 0) {
            return null;
        }

        $get_template = $this->get_template;
        $embed_html = static function (string $tooltip_id, string $component_html) use ($tooltips, $get_template): string {
            $tpl = $get_template("src/UI/templates/default/tpl.tooltip.html", true, true);
            $tpl->setVariable("ELEMENT", $component_html);
            $tpl->setVariable("TOOLTIP_ID", $tooltip_id);

            foreach ($tooltips as $tooltip) {
                $tpl->setCurrentBlock("tooltip");
                $tpl->setVariable("TOOLTIP", $tooltip);
                $tpl->parseCurrentBlock();
            }

            return $tpl->get();
        };

        $embed_js = static function ($id) {
            return "new il.UI.core.Tooltip(document.getElementById('$id'));";
        };

        return [$embed_html, $embed_js];
    }
}
