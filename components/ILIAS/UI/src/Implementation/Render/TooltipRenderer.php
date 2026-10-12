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

namespace ILIAS\UI\Implementation\Render;

use ILIAS\UI\HelpTextRetriever;
use ILIAS\UI\Help;

/**
 * This class is supposed to unify rendering of tooltips over all components
 * and should also be usable by legacy UI components.
 */
class TooltipRenderer
{
    protected const string ROLE_TOOLTIP = 'tooltip';
    protected const string ROLE_STATUS = 'status';

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
     * @example
     *      $tooltip_embedding = $this->maybeGetTooltipEmbedding(...$component->getHelpTopics());
     *      if (null !== $tooltip_embedding) {
     *          [$tooltip_html, $tooltip_js] = $tooltip_embedding;
     *          $component = $component->withAdditionalOnLoadCode($tooltip_js);
     *          // generate tooltip id and possibly wire component html with 'aria-describedby'
     *          $component_html = $tooltip_html($tooltip_id, $component_html);
     *      }
     *      return $component_html;
     *
     * @return array{0: Closure(string, string): string, 1: Closure(string): string}|null (HTML, JS)
     */
    public function getHelpTopicTooltipEmbedding(Help\Topic ...$topics): ?array
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
            $tpl = $get_template("components/ILIAS/UI/src/templates/default/tpl.tooltip.html", true, true);
            $tpl->setVariable("COMPONENT_HTML", $component_html);
            $tpl->setVariable("TOOLTIP_ROLE", self::ROLE_TOOLTIP);
            $tpl->setVariable("TOOLTIP_ID", $tooltip_id);

            foreach ($tooltips as $tooltip) {
                $tpl->setCurrentBlock("tooltip");
                $tpl->setVariable("TOOLTIP", $tooltip);
                $tpl->parseCurrentBlock();
            }

            return $tpl->get();
        };

        $embed_js = static function (string $component_id) {
            return "il.UI.Tooltip.createHoverTooltip(document.getElementById('$component_id'));";
        };

        return [$embed_html, $embed_js];
    }

    /**
     * This will provide functions that can be used to embed a components html
     * into some html required for the tooltip. The first resulting function
     * takes an id to be used as the tooltips id and the html of the component.
     * The second resulting function takes the id of the component and creates
     * an appropriate javascript to bind the required javascript to the component.
     *
     * @example
     *      // this should be the consumers component already, only for example:
     *      $component = $this->getUIFactory()->button()->standard();
     *
     *      [$tooltip_html, $tooltip_js] = $this->getTooltipEmbedding('This is a tooltip!');
     *      $component = $component->withAdditionalOnLoadCode($tooltip_js);
     *      // generate tooltip id and possibly wire component html with 'aria-describedby'
     *      $component_html = $tooltip_html($tooltip_id, $component_html);
     *      return $component_html;
     *
     * @return array{0: Closure(string, string): string, 1: Closure(string): string}
     */
    public function getStatusTooltipEmbedding(string $tooltip): array
    {
        $get_template = $this->get_template;
        $embed_html = static function (string $tooltip_id, string $component_html) use ($tooltip, $get_template): string {
            $tpl = $get_template("components/ILIAS/UI/src/templates/default/tpl.tooltip.html", true, true);
            $tpl->setVariable("COMPONENT_HTML", $component_html);
            $tpl->setVariable("TOOLTIP_ROLE", self::ROLE_STATUS);
            $tpl->setVariable("TOOLTIP_ID", $tooltip_id);
            $tpl->setVariable("TOOLTIP", $tooltip);

            return $tpl->get();
        };

        $embed_js = static function (string $component_id) {
            return "il.UI.Tooltip.createClickTooltip(document.getElementById('$component_id'));";
        };

        return [$embed_html, $embed_js];
    }
}
