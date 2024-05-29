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
use LogicException;

class Renderer extends AbstractComponentRenderer
{
    public const DEFAULT_DROPDOWN_LABEL = 'label_fieldselection';
    public const DEFAULT_BUTTON_LABEL = 'label_fieldselection_refresh';
    public const DEFAULT_SORTATION_DROPDOWN_LABEL = 'label_sortation';
    public const DEFAULT_DROPDOWN_LABEL_OFFSET = 'label_pagination_offset';
    public const DEFAULT_DROPDOWN_LABEL_LIMIT = 'label_pagination_limit';

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
            case ($component instanceof Component\Input\ViewControl\Group):
                return $default_renderer->render($component->getInputs());
            case ($component instanceof Component\Input\ViewControl\NullControl):
                return '';

            default:
                throw new LogicException("Cannot render '" . get_class($component) . "'");
        }
    }

    protected function getComponentInterfaceName(): array
    {
        return [
            Component\Input\ViewControl\FieldSelection::class,
            Component\Input\ViewControl\Sortation::class,
            Component\Input\ViewControl\Pagination::class,
            Component\Input\ViewControl\Group::class,
            Component\Input\ViewControl\NullControl::class,
        ];
    }

    protected function renderFieldSelection(FieldSelection $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.viewcontrol_fieldselection.html", true, true);
        $ui_factory = $this->getUIFactory();

        $set_values = $component->getValue() ?? [];
        foreach ($component->getOptions() as $opt_value => $opt_label) {
            $tpl->setCurrentBlock("option");
            $tpl->setVariable("OPTION_ID", $this->getJavascriptBinding()->createId());
            $tpl->setVariable("OPTION_VALUE", $opt_value);
            $tpl->setVariable("OPTION_LABEL", $opt_label);
            if (in_array($opt_value, $set_values)) {
                $tpl->setVariable("CHECKED", 'checked');
            }
            $tpl->parseCurrentBlock();

            if (in_array($opt_value, $set_values)) {
                $tpl->setCurrentBlock("value");
                $tpl->setVariable("NAME", $component->getName());
                $tpl->setVariable("VALUE", $opt_value);
                $tpl->parseCurrentBlock();
            }
        }

        $internal_signal = $component->getInternalSignal();
        $param_name = $component->getName();
        if ($container_submit_signal = $component->getOnChangeSignal()) {
            $component = $component->withAdditionalOnLoadCode(
                fn ($id) => "$(document).on('{$internal_signal}', 
                    function(event, signal_data) {
                        var container = event.target.closest('.il-viewcontrol-fieldselection'),
                            checkbox = container.querySelectorAll('input[type=checkbox]'),
                            value = Object.values(checkbox).map(o => o.checked ? o.value : ''),
                            value_container = container.querySelector('.il-viewcontrol-value');

                        value_container.innerHTML = '';
                        value.forEach(function(v){
                            let element = document.createElement('input');
                            element.type = 'hidden';
                            element.name = '{$param_name}[]';
                            element.value = v;
                            value_container.appendChild(element);
                        });
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
        $button_label = $component->getButtonLabel() !== '' ?
            $component->getButtonLabel() : $this->txt(self::DEFAULT_BUTTON_LABEL);
        $button = $ui_factory->button()->standard($button_label, '#')
            ->withOnClick($internal_signal);

        $tpl->setVariable('ID', $id);
        $tpl->setVariable("ID_MENU", $id . '_ctrl');
        $tpl->setVariable("ARIA_LABEL", $this->txt(self::DEFAULT_DROPDOWN_LABEL));
        $tpl->setVariable("BUTTON", $default_renderer->render($button));

        return $tpl->get();
    }

    protected function renderSortation(Sortation $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.viewcontrol_sortation.html", true, true);
        $ui_factory = $this->getUIFactory();

        foreach ($component->getOptions() as $opt_label => $order) {
            $opt_value = $order->join(':', fn ($ret, $key, $value) => implode($ret, [$key, $value]));
            $internal_signal = $component->getInternalSignal();
            $internal_signal->addOption('value', $opt_value);
            $item = $ui_factory->button()->shy((string) $opt_label, '#')
                ->withOnClick($internal_signal);
            $tpl->setCurrentBlock("option");
            $tpl->setVariable("OPTION", $default_renderer->render($item));
            if ($opt_value === $component->getValue()) {
                $tpl->touchBlock("selected");
                $tpl->setCurrentBlock("option");
            }
            $tpl->parseCurrentBlock();
        }

        if ($container_submit_signal = $component->getOnChangeSignal()) {
            $component = $component->withAdditionalOnLoadCode(
                fn ($id) => "$(document).on('{$internal_signal}', 
                    function(event, signal_data) { 
                        let container;
                        if(signal_data.options.parent_container) {
                            container =  document.querySelector(
                                '#' + signal_data.options.parent_container 
                                + ' .il-viewcontrol-sortation'
                            );
                        } else {
                             container = event.target.closest('.il-viewcontrol-sortation');
                        }
                        let inputs = container.querySelectorAll('.il-viewcontrol-value > input');
                        let val = signal_data.options.value.split(':');
                        inputs[0].value = val[0];
                        inputs[1].value = val[1];
                        $(event.target).trigger('{$container_submit_signal}');
                        return false;
                    });"
            );
        }
        $id = $this->bindJavaScript($component);

        $tpl->setVariable('ID', $id);
        $tpl->setVariable("ID_MENU", $id . '_ctrl');
        $tpl->setVariable("ARIA_LABEL", $this->txt(self::DEFAULT_SORTATION_DROPDOWN_LABEL));

        $tpl->setVariable(
            "VALUES",
            $default_renderer->render(
                $component->getInputGroup()
            )
        );

        return $tpl->get();
    }

    /**
     * @return \ILIAS\Data\Range[]
     */
    protected function buildRanges(
        int $total_count,
        int $page_limit
    ): array {
        $data_factory = $this->getDataFactory();
        if ($page_limit >= $total_count) {
            return [$data_factory->range(0, $page_limit)];
        }
        foreach (range(0, $total_count - 1, $page_limit) as $idx => $start) {
            $ranges[] = $data_factory->range($start, $page_limit);
        }
        return $ranges;
    }

    /**
     * @param \ILIAS\Data\Range[] $ranges
     */
    protected function findCurrentPage(array $ranges, int $offset): int
    {
        foreach ($ranges as $idx => $range) {
            if ($offset >= $range->getStart() && $offset < $range->getEnd()) {
                return $idx;
            }
        }
        throw new LogicException('offset is not in any range');
    }

    /**
     * @param \ILIAS\Data\Range[] $ranges
     * @return \ILIAS\Data\Range[]
     */
    protected function sliceRangesToVisibleEntries(array $ranges, int $current, int $number_of_visible_entries): array
    {
        $first = reset($ranges);
        $last = end($ranges);

        $start = max(0, $current - floor(($number_of_visible_entries - 1) / 2));
        if ($start + $number_of_visible_entries >= count($ranges)) {
            $start = max(0, count($ranges) - $number_of_visible_entries);
        }

        $entries = array_slice($ranges, (int) $start, $number_of_visible_entries);

        if (! in_array($first, $entries)) {
            array_shift($entries);
            array_unshift($entries, $first);
        }
        if (! in_array($last, $entries)) {
            array_pop($entries);
            array_push($entries, $last);
        }
        return $entries;
    }

    protected function renderPagination(Pagination $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.viewcontrol_pagination.html", true, true);
        $ui_factory = $this->getUIFactory();
        $internal_signal = $component->getInternalSignal();
        $limit_options = $component->getLimitOptions();
        $total_count = $component->getTotalCount();

        list(Pagination::FNAME_OFFSET => $offset, Pagination::FNAME_LIMIT => $limit) = array_map('intval', $component->getValue());
        $limit = $limit > 0 ? $limit : reset($limit_options);

        if (! $total_count) {
            $input = $ui_factory->input()->field()->numeric('offset')->withValue($offset);
            $apply = $ui_factory->button()->standard('apply', '');
            $tpl->setVariable("INPUT", $default_renderer->render($input));
            $tpl->setVariable("BUTTON", $default_renderer->render($apply));
        } else {
            $ranges = $this->buildRanges($total_count, $limit);
            $current = $this->findCurrentPage($ranges, $offset);

            if ($limit >= $total_count) {
                $entries = $ranges;
            } else {
                $entries = $this->sliceRangesToVisibleEntries($ranges, $current, $component->getNumberOfVisibleEntries());
            }

            foreach ($ranges as $idx => $range) {
                if (in_array($range, $entries)) {
                    $signal = clone $internal_signal;
                    $signal->addOption('offset', $range->getStart());
                    $signal->addOption('limit', $limit);
                    $tpl->setCurrentBlock("entry");
                    $entry = $ui_factory->button()->shy((string) ($idx + 1), '#')->withOnClick($signal);
                    if ($idx === $current) {
                        $entry = $entry->withEngagedState(true);
                    }
                    $tpl->setVariable("ENTRY", $default_renderer->render($entry));
                    $tpl->parseCurrentBlock();
                } else {
                    if ($idx === 1 || $idx === count($ranges) - 2) {
                        $tpl->setCurrentBlock("entry");
                        $tpl->touchBlock("spacer");
                        $tpl->parseCurrentBlock();
                    }
                }
            }

            $icon_left = $ui_factory->symbol()->glyph()->back();
            if ($current > 0 && count($entries) > 1) {
                $range = $ranges[$current - 1];
                $signal = clone $internal_signal;
                $signal->addOption('offset', $range->getStart());
                $signal->addOption('limit', $limit);
                $icon_left = $icon_left ->withOnClick($signal);
            } else {
                $icon_left = $icon_left->withUnavailableAction();
                $tpl->touchBlock('left_disabled');
            }
            $tpl->setVariable("LEFT", $default_renderer->render($icon_left));

            $icon_right = $ui_factory->symbol()->glyph()->next();
            if ($current < count($ranges) - 1) {
                $range = $ranges[$current + 1];
                $signal = clone $internal_signal;
                $signal->addOption('offset', $range->getStart());
                $signal->addOption('limit', $limit);
                $icon_right = $icon_right ->withOnClick($signal);
            } else {
                $icon_right = $icon_right->withUnavailableAction();
                $tpl->touchBlock('right_disabled');
            }
            $tpl->setVariable("RIGHT", $default_renderer->render($icon_right));
        }

        foreach ($component->getLimitOptions() as $option) {
            $signal = clone $internal_signal;
            $signal->addOption('offset', $offset);
            $signal->addOption('limit', (string) $option);
            $option_label = $option === \PHP_INT_MAX ? $this->txt('ui_pagination_unlimited') : (string) $option;

            $item = $ui_factory->button()->shy($option_label, '#')
                ->withOnClick($signal);
            $tpl->setCurrentBlock("option_limit");
            $tpl->setVariable("OPTION_LIMIT", $default_renderer->render($item));
            if ($option === $limit) {
                $tpl->touchBlock("selected");
                $tpl->setCurrentBlock("option_limit");
            }
            $tpl->parseCurrentBlock();
        }

        if ($container_submit_signal = $component->getOnChangeSignal()) {
            $component = $component->withAdditionalOnLoadCode(
                fn ($id) => "$(document).on('{$internal_signal}',
                    function(event, signal_data) {
                        let inputs = event.target
                            .closest('.il-viewcontrol-pagination')
                            .querySelectorAll('.il-viewcontrol-value input');
                        inputs[0].value = signal_data.options.offset;
                        inputs[1].value = signal_data.options.limit;

                        $(event.target).trigger('{$container_submit_signal}');
                        return false;
                    });"
            );
        }
        $id = $this->bindJavaScript($component);

        $tpl->setVariable('ID', $id);
        $tpl->setVariable("ID_MENU_OFFSET", $id . '_ctrl_offset');
        $tpl->setVariable("ARIA_LABEL_OFFSET", $this->txt(self::DEFAULT_DROPDOWN_LABEL_OFFSET));
        $tpl->setVariable("ID_MENU_LIMIT", $id . '_ctrl_limit');
        $tpl->setVariable("ARIA_LABEL_LIMIT", $this->txt(self::DEFAULT_DROPDOWN_LABEL_LIMIT));

        $tpl->setVariable(
            "VALUES",
            $default_renderer->render(
                $component->getInputGroup()
            )
        );

        return $tpl->get();
    }
}
