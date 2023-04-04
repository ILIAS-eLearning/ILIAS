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

namespace ILIAS\UI\Implementation\Component\Input\ViewControl;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

class Renderer extends AbstractComponentRenderer
{
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        $this->checkComponent($component);
        switch (true) {
            case ($component instanceof FieldSelection):
                return $this->renderFieldSelection($component, $default_renderer);
            case ($component instanceof Sortation):
                return $this->renderSortation($component, $default_renderer);
            case ($component instanceof Pagination):
                return $this->renderPagination($component, $default_renderer);
            default:
                throw new LogicException("Cannot render '" . get_class($component) . "'");
        }
    }

    protected function getComponentInterfaceName(): array
    {
        return [
            Component\Input\ViewControl\FieldSelection::class,
            Component\Input\ViewControl\Sortation::class,
            Component\Input\ViewControl\Pagination::class
        ];
    }

    protected function renderFieldSelection(FieldSelection $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.vc_fieldselection.html", true, true);
        $ui_factory = $this->getUIFactory();
        $icon = $ui_factory->symbol()->glyph()->bulletlist(); //->withHighlight();

        $set_values = $component->getValue() ? array_filter(explode(',', $component->getValue())) : [];
        foreach ($component->getOptions() as $opt_value => $opt_label) {
            $tpl->setCurrentBlock("option");
            $tpl->setVariable("OPTION_ID", $this->getJavascriptBinding()->createId());
            $tpl->setVariable("OPTION_VALUE", $opt_value);
            $tpl->setVariable("OPTION_LABEL", $opt_label);
            if (in_array($opt_value, $set_values)) {
                $tpl->setVariable("CHECKED", 'checked');
            }
            $tpl->parseCurrentBlock();
        }

        $internal_signal = $component->getInternalSignal();

        if ($container_submit_signal = $component->getOnChangeSignal()) {
            $component = $component->withAdditionalOnLoadCode(
                fn ($id) => "$(document).on('{$internal_signal}', 
                    function(event, signal_data) {
                        var container = event.target.closest('.il-viewcontrol-fieldselection'),
                            checked = container.querySelectorAll('input[type=checkbox]:checked'),
                            value = Object.values(checked).map(o => o.value);

                        container.querySelector('.il-viewcontrol-value > input').value = value.join(',');
                        $(event.target).trigger('{$container_submit_signal}');
                        return false;
                    });"
            );
        }

        $component = $component->withAdditionalOnLoadCode(
            fn ($id) => "$('#{$id} > .dropdown-menu')
                .on('click', (event) =>  event.stopPropagation());"
        );

        $id = $this->bindJavaScript($component);
        $container_submit_signal = $component->getOnChangeSignal();
        $button = $ui_factory->button()->standard($component->getButtonLabel(), '#')
            ->withOnClick($internal_signal);

        $tpl->setVariable('ID', $id);
        $tpl->setVariable("ID_MENU", $id . '_ctrl');
        $tpl->setVariable("ARIA_LABEL", $component->getLabel());
        $tpl->setVariable("CONTROL_LABEL", $default_renderer->render($icon));
        $tpl->setVariable("BUTTON", $default_renderer->render($button));
        $tpl->setVariable("NAME", $component->getName());
        $tpl->setVariable("VALUE", $component->getValue());

        if ($component->isDisabled()) {
            $tpl->touchBlock("disabled");
        }

        return $tpl->get();
    }

    protected function renderSortation(Sortation $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.vc_sortation.html", true, true);
        $ui_factory = $this->getUIFactory();
        $icon = $ui_factory->symbol()->glyph()->sortation();

        foreach ($component->getOptions() as $opt_value => $opt_label) {
            $internal_signal = $component->getInternalSignal();
            $internal_signal->addOption('value', $opt_value);
            $item = $ui_factory->button()->shy((string)$opt_label, '#')
                ->withOnClick($internal_signal);
            $tpl->setCurrentBlock("option");
            $tpl->setVariable("OPTION", $default_renderer->render($item));
            $tpl->parseCurrentBlock();
        }

        if ($container_submit_signal = $component->getOnChangeSignal()) {
            $component = $component->withAdditionalOnLoadCode(
                fn ($id) => "$(document).on('{$internal_signal}', 
                    function(event, signal_data) { 
                        event.target
                            .closest('.il-viewcontrol-sortation')
                            .querySelector('.il-viewcontrol-value > input')
                            .value = signal_data.options.value;
                        $(event.target).trigger('{$container_submit_signal}');
                        return false;
                    });"
            );
        }
        $id = $this->bindJavaScript($component);

        $tpl->setVariable('ID', $id);
        $tpl->setVariable("ID_MENU", $id . '_ctrl');
        $tpl->setVariable("ARIA_LABEL", $component->getLabel());
        $tpl->setVariable("CONTROL_LABEL", $default_renderer->render($icon));
        $tpl->setVariable("NAME", $component->getName());
        $tpl->setVariable("VALUE", $component->getValue());

        if ($component->isDisabled()) {
            $tpl->touchBlock("disabled");
        }

        return $tpl->get();
    }


    protected function renderPagination(Pagination $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.vc_pagination.html", true, true);
        $ui_factory = $this->getUIFactory();
        $data_factory = $this->getDataFactory();

        $internal_signal = $component->getInternalSignal();

        $limit_options = $component->getLimitOptions();
        $set_value = $component->getValue() ?? '0:' . end($limit_options);//$component->getDefaultValue();
        list($offset, $limit) = array_map('intval', explode(':', $set_value));

        $infinite = $limit === \PHP_INT_MAX;

        if (! $infinite && !$component->isDisabled()) {
            $ranges = [];

            if ($limit + 1 > $offset) {
                $ranges[] = $data_factory->range(0, $limit);
            } else {
                foreach (range(0, $offset, $limit + 1) as $offset) {
                    $ranges[] = $data_factory->range($offset, $limit);
                }
            }

            foreach ($ranges as $range) {
                $signal = clone $internal_signal;
                $signal->addOption('offset', $range->getStart());
                $signal->addOption('limit', $limit);

                $tpl->setCurrentBlock("option_offset");
                $label = $range->getStart() + 1 . '-' . $range->getEnd() + 1;
                $item = $ui_factory->button()->shy($label, '#')->withOnClick($signal);
                $tpl->setVariable("OPTION_OFFSET", $default_renderer->render($item));
                $tpl->parseCurrentBlock();
            }
            $tpl->setVariable("CONTROL_LABEL_OFFSET", $label);

            $signal = clone $internal_signal;
            $signal->addOption('offset', $range->getEnd() + 1);
            $signal->addOption('limit', $limit);

            $icon_right = $ui_factory->symbol()->glyph()->next()->withOnClick($signal);
            $tpl->setVariable("RIGHT", $default_renderer->render($icon_right));

            if (count($ranges) > 1) {
                $range = $ranges[count($ranges) - 2];
                $signal = clone $internal_signal;
                $signal->addOption('offset', $range->getStart());
                $signal->addOption('limit', $limit);
                $icon_left = $ui_factory->symbol()->glyph()->back()->withOnClick($signal);
                $tpl->setVariable("LEFT", $default_renderer->render($icon_left));
            }
        }

        $icon = $ui_factory->symbol()->glyph()->numberedlist();
        $tpl->setVariable("CONTROL_LABEL_LIMIT", $default_renderer->render($icon));
        foreach ($component->getLimitOptions() as $option) {
            $signal = clone $internal_signal;
            $signal->addOption('offset', $offset);
            $signal->addOption('limit', (string)$option);
            $option_label = $option === \PHP_INT_MAX ? 'unlimited' : (string)$option;

            $item = $ui_factory->button()->shy($option_label, '#')
                ->withOnClick($signal);
            $tpl->setCurrentBlock("option_limit");
            $tpl->setVariable("OPTION_LIMIT", $default_renderer->render($item));
            $tpl->parseCurrentBlock();
        }


        if ($container_submit_signal = $component->getOnChangeSignal()) {
            $component = $component->withAdditionalOnLoadCode(
                fn ($id) => "$(document).on('{$internal_signal}',
                    function(event, signal_data) {
                        event.target
                            .closest('.il-viewcontrol-pagination')
                            .querySelector('.il-viewcontrol-value > input')
                            .value = signal_data.options.offset + ':' + signal_data.options.limit;
                        $(event.target).trigger('{$container_submit_signal}');
                        return false;
                    });"
            );
        }
        $id = $this->bindJavaScript($component);

        $tpl->setVariable('ID', $id);
        $tpl->setVariable("ID_MENU_OFFSET", $id . '_ctrl_offset');
        $tpl->setVariable("ARIA_LABEL_OFFSET", $component->getLabel());
        $tpl->setVariable("ID_MENU_LIMIT", $id . '_ctrl_limit');
        $tpl->setVariable("ARIA_LABEL_LIMIT", $component->getLabelLimit());
        $tpl->setVariable("CONTROL_LABEL", $default_renderer->render($icon));
        $tpl->setVariable("NAME", $component->getName());
        $tpl->setVariable("VALUE", $component->getValue());

        if ($component->isDisabled()) {
            $tpl->touchBlock("disabled_limit");
        }

        return $tpl->get();
    }
}
