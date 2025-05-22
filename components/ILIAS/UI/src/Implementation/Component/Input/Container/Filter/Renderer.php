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

namespace ILIAS\UI\Implementation\Component\Input\Container\Filter;

use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Component\Button\Toggle;
use ILIAS\UI\Implementation\Component\Input\Container\Filter;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\Template;
use ILIAS\UI\Renderer as RendererInterface;
use LogicException;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        if ($component instanceof Filter\Standard) {
            return $this->renderStandard($component, $default_renderer);
        }

        $this->cannotHandleComponent($component);
    }

    /**
     * Render standard filter
     */
    protected function renderStandard(Filter\Standard $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.standard_filter.html", true, true);

        // JavaScript
        $component = $this->registerSignals($component);
        /**
         * @var $component Filter\Standard
         */
        $id = $this->bindJavaScript($component);
        $tpl->setVariable('ID_FILTER', $id);

        // render expand and collapse
        $this->renderExpandAndCollapse($tpl, $component, $default_renderer);

        // render apply and reset buttons
        $this->renderApplyAndReset($tpl, $component, $default_renderer);

        // render toggle button
        $this->renderToggleButton($tpl, $component, $default_renderer);

        // render inputs
        $this->renderInputs($tpl, $component, $id, $default_renderer);

        return $tpl->get();
    }

    protected function registerSignals(Filter\Filter $filter): Filter\Filter
    {
        $update = $filter->getUpdateSignal();
        return $filter->withAdditionalOnLoadCode(fn($id) => "$(document).on('$update', function(event, signalData) {
                il.UI.filter.onInputUpdate(event, signalData, '$id'); return false; 
            });");
    }

    /**
     * Render expand/collapse section
     *
     * @param Template $tpl
     * @param Filter\Standard $component
     * @param RendererInterface $default_renderer
     */
    protected function renderExpandAndCollapse(
        Template $tpl,
        Filter\Standard $component,
        RendererInterface $default_renderer
    ): void {
        $f = $this->getUIFactory();

        $tpl->setCurrentBlock("action");
        $tpl->setVariable("ACTION_NAME", "expand");
        $tpl->setVariable("ACTION", $component->getExpandAction());
        $tpl->parseCurrentBlock();

        $tpl->setCurrentBlock("action");
        $tpl->setVariable("ACTION_NAME", "collapse");
        $tpl->setVariable("ACTION", $component->getCollapseAction());
        $tpl->parseCurrentBlock();

        $tpl->setVariable("TITLE_FILTER", $this->txt("filter"));
        $glyph_collapse = $f->symbol()->glyph()->collapse();
        $tpl->setVariable("COLLAPSE_GLYPH", $default_renderer->render($glyph_collapse));
        $glyph_expand = $f->symbol()->glyph()->expand();
        $tpl->setVariable("EXPAND_GLYPH", $default_renderer->render($glyph_expand));

        $is_expanded = $component->isExpanded();
        $tpl->setVariable("ARIA_EXPANDED", $is_expanded ? "true" : "false");
        $tpl->setVariable("COLLAPSE_GLYPH_VISIBLE", $is_expanded ? 1 : 0);
        $tpl->setVariable("EXPAND_GLYPH_VISIBLE", $is_expanded ? 0 : 1);
        $tpl->setVariable("ACTIVE_INPUTS_EXPANDED", $is_expanded ? 0 : 1);
        $tpl->setVariable("SECTION_INPUTS_EXPANDED", $is_expanded ? 1 : 0);
    }

    /**
     * Render apply and reset
     */
    protected function renderApplyAndReset(
        Template $tpl,
        Filter\Standard $component,
        RendererInterface $default_renderer
    ): void {
        $f = $this->getUIFactory();

        $tpl->setCurrentBlock("action");
        $tpl->setVariable("ACTION_NAME", "apply");
        $tpl->setVariable("ACTION", $component->getApplyAction());
        $tpl->parseCurrentBlock();

        // render apply and reset buttons
        $apply = $f->button()->bulky($f->symbol()->glyph()->apply(), $this->txt("apply"), "")
            ->withOnLoadCode(fn($id) => "$('#$id').on('click', function(event) {
                        il.UI.filter.onCmd(event, '$id', 'apply');
                        return false; // stop event propagation
                });
                $('#$id').closest('.il-filter').find(':text').on('keypress', function(ev) {
                    if (typeof ev != 'undefined' && typeof ev.keyCode != 'undefined' && ev.keyCode == 13) {
                        il.UI.filter.onCmd(event, '$id', 'apply');
                        return false; // stop event propagation
                    }
                });
                ");
        $reset = $f->button()->bulky($f->symbol()->glyph()->reset(), $this->txt("reset"), $component->getResetAction());

        $tpl->setVariable("APPLY", $default_renderer->render($apply));
        $tpl->setVariable("RESET", $default_renderer->render($reset));
    }

    /**
     * Render toggle button
     */
    protected function renderToggleButton(
        Template $tpl,
        Filter\Standard $component,
        RendererInterface $default_renderer
    ): void {
        $f = $this->getUIFactory();

        $tpl->setCurrentBlock("action");
        $tpl->setVariable("ACTION_NAME", "toggleOn");
        $tpl->setVariable("ACTION", $component->getToggleOnAction());
        $tpl->parseCurrentBlock();

        $tpl->setCurrentBlock("action");
        $tpl->setVariable("ACTION_NAME", "toggleOff");
        $tpl->setVariable("ACTION", $component->getToggleOffAction());
        $tpl->parseCurrentBlock();

        $signal_generator = new SignalGenerator();
        $toggle_on_signal = $signal_generator->create();
        $toggle_off_signal = $signal_generator->create();
        /**
         * @var $toggle Toggle
         */
        $toggle = $f->button()->toggle("", $toggle_on_signal, $toggle_off_signal, $component->isActivated());
        $toggle = $toggle->withAdditionalOnLoadCode(fn($id) => "$(document).on('$toggle_on_signal',function(event) {
                        il.UI.filter.onCmd(event, '$id', 'toggleOn');
                        return false; // stop event propagation
            });");
        $toggle = $toggle->withAdditionalOnLoadCode(fn($id) => "$(document).on('$toggle_off_signal',function(event) {
                        il.UI.filter.onCmd(event, '$id', 'toggleOff');
                        return false; // stop event propagation
            });");

        $tpl->setVariable("TOGGLE", $default_renderer->render($toggle));
    }

    /**
     * Render inputs
     */
    protected function renderInputs(
        Template $tpl,
        Filter\Standard $component,
        string $component_id,
        RendererInterface $default_renderer
    ): void {
        // pass information on what inputs should be initially rendered
        $is_input_rendered = $component->isInputRendered();
        foreach ($component->getInputs() as $k => $input) {
            $is_rendered = current($is_input_rendered);
            $tpl->setCurrentBlock("status");
            $tpl->setVariable("FIELD", $k);
            $tpl->setVariable("VALUE", (int) $is_rendered);
            $tpl->parseCurrentBlock();
            next($is_input_rendered);
        }

        // render inputs
        $input_group = $component->getInputGroup();
        if ($component->isActivated()) {
            $tpl->touchBlock("enabled");
        } else {
            $tpl->touchBlock("disabled");
        }
        for ($i = 1; $i <= count($component->getInputs()); $i++) {
            $tpl->setCurrentBlock("active_inputs");
            $tpl->setVariable("ID_INPUT_ACTIVE", $i);
            $tpl->parseCurrentBlock();
        }
        if (count($component->getInputs()) > 0) {
            $tpl->setCurrentBlock("active_inputs_section");
            $tpl->setVariable("ID_FILTER_ACTIVE", $component_id);
            $tpl->parseCurrentBlock();
        }

        $input_group = $input_group->withOnUpdate($component->getUpdateSignal());

        $tpl->setVariable("INPUTS", $default_renderer->render($input_group));
    }
}
