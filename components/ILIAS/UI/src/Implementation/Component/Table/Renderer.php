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

namespace ILIAS\UI\Implementation\Component\Table;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Render\Template;
use ILIAS\Data\Order;
use ILIAS\Data\URI;
use ILIAS\UI\Implementation\Component\Table\Action\Action;
use ILIAS\UI\Implementation\Component\Input\ViewControl\Pagination;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        $this->checkComponent($component);
        if ($component instanceof Component\Table\Presentation) {
            return $this->renderPresentationTable($component, $default_renderer);
        }
        if ($component instanceof Component\Table\PresentationRow) {
            return $this->renderPresentationRow($component, $default_renderer);
        }
        if ($component instanceof Component\Table\Data) {
            return $this->renderDataTable($component, $default_renderer);
        }
        if ($component instanceof Component\Table\DataRow) {
            return $this->renderDataRow($component, $default_renderer);
        }
        throw new \LogicException(self::class . " cannot render component '" . get_class($component) . "'.");
    }

    protected function renderPresentationTable(
        Component\Table\Presentation $component,
        RendererInterface $default_renderer
    ): string {
        $tpl = $this->getTemplate("tpl.presentationtable.html", true, true);
        $tpl->setVariable("TITLE", $component->getTitle());
        $expcollapsebtns = [];
        if ($sig_ta = $component->getExpandCollapseAllSignal()) {
            $sig_ta_expand = clone $sig_ta;
            $sig_ta_expand->addOption('expand', true);
            $expcollapsebtns[] = $this->getUIFactory()->button()
                ->standard($this->txt('presentation_table_expand'), '')
                ->withOnClick($sig_ta_expand);
            $sig_ta_collapse = clone $sig_ta;
            $sig_ta_collapse->addOption('expand', false);
            $expcollapsebtns[] = $this->getUIFactory()->button()
                ->standard($this->txt('presentation_table_collapse'), '')
                ->withOnClick($sig_ta_collapse);
            $component = $component->withAdditionalOnLoadCode(
                static fn($id) => "
                    il.UI.table.presentation.init('{$id}');
                    $(document).on('$sig_ta', function(event, signal_data) { il.UI.table.presentation.get('$id').expandAll(signal_data); return false; });
                    "
            );
        }

        $tpl->setVariable("EXPANDCOLLAPSEALL", $default_renderer->render($expcollapsebtns));

        $vcs = $component->getViewControls();
        if ($vcs) {
            $tpl->setVariable("VC", $default_renderer->render($vcs));
        }

        $id = $this->bindJavaScript($component);
        $tpl->setVariable("ID", $id);

        $row_mapping = $component->getRowMapping();
        $data = $component->getData();
        $component_id = $id;

        if (empty($data)) {
            $this->renderEmptyPresentationRow($tpl, $default_renderer, $this->txt('ui_table_no_records'));
            return $tpl->get();
        }

        foreach ($data as $record) {
            $row = $row_mapping(
                new PresentationRow($component->getSignalGenerator(), $component_id),
                $record,
                $this->getUIFactory(),
                $component->getEnvironment()
            );

            $tpl->setCurrentBlock("row");
            $tpl->setVariable("ROW", $default_renderer->render($row));
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }

    protected function renderPresentationRow(
        Component\Table\PresentationRow $component,
        RendererInterface $default_renderer
    ): string {
        $f = $this->getUIFactory();
        $tpl = $this->getTemplate("tpl.presentationrow.html", true, true);

        $component = $this->registerSignals($component->withResetSignals());
        $sig_show = $component->getShowSignal();
        $sig_hide = $component->getCloseSignal();
        $sig_toggle = $component->getToggleSignal();
        $id = $this->bindJavaScript($component);

        $expander = $f->symbol()->glyph()->expand("#")
            ->withOnClick($sig_show);
        $collapser = $f->symbol()->glyph()->collapse("#")
            ->withOnClick($sig_hide);
        $shy_expander = $f->button()->shy($this->txt("presentation_table_more"), "#")
            ->withOnClick($sig_show);

        $tpl->setVariable("ID", $id);
        $tpl->setVariable("EXPANDER", $default_renderer->render($expander));
        $tpl->setVariable("COLLAPSER", $default_renderer->render($collapser));
        $tpl->setVariable("SHY_EXPANDER", $default_renderer->render($shy_expander));

        if ($symbol = $component->getLeadingSymbol()) {
            $tpl->setVariable("SYMBOL", $default_renderer->render($symbol));
        }
        $tpl->setVariable("HEADLINE", $component->getHeadline());
        $tpl->setVariable("TOGGLE_SIGNAL", $sig_toggle);
        $subheadline = $component->getSubheadline();
        if ($subheadline) {
            $tpl->setVariable("SUBHEADLINE", $subheadline);
        }

        foreach ($component->getImportantFields() as $label => $value) {
            $tpl->setCurrentBlock("important_field");
            if (is_string($label)) {
                $tpl->setVariable("IMPORTANT_FIELD_LABEL", $label);
            }
            $tpl->setVariable("IMPORTANT_FIELD_VALUE", $value);
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable("DESCLIST", $default_renderer->render($component->getContent()));

        $further_fields_headline = $component->getFurtherFieldsHeadline();
        $further_fields = $component->getFurtherFields();

        if (count($further_fields) > 0) {
            $tpl->touchBlock("has_further_fields");

            if ($further_fields_headline) {
                $tpl->setVariable("FURTHER_FIELDS_HEADLINE", $further_fields_headline);
            }

            foreach ($further_fields as $label => $value) {
                $tpl->setCurrentBlock("further_field");
                if (is_string($label)) {
                    $tpl->setVariable("FIELD_LABEL", $label);
                }
                $tpl->setVariable("FIELD_VALUE", $value);
                $tpl->parseCurrentBlock();
            }
        }

        $action = $component->getAction();
        if (!is_null($action)) {
            $tpl->setCurrentBlock("button");
            $tpl->setVariable("BUTTON", $default_renderer->render($action));
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }

    public function renderDataTable(Component\Table\Data $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.datatable.html", true, true);

        $opt_action_id = Action::OPT_ACTIONID;
        $opt_row_id = Action::OPT_ROWID;
        $component = $component
            ->withAdditionalOnLoadCode(
                static fn($id): string =>
                    "il.UI.table.data.init('{$id}','{$opt_action_id}','{$opt_row_id}');"
            )
            ->withAdditionalOnLoadCode($this->getAsyncActionHandler($component->getAsyncActionSignal()))
            ->withAdditionalOnLoadCode($this->getMultiActionHandler($component->getMultiActionSignal()))
            ->withAdditionalOnLoadCode($this->getSelectionHandler($component->getSelectionSignal()));

        $actions = [];
        foreach ($component->getAllActions() as $action_id => $action) {
            $component = $component->withAdditionalOnLoadCode($this->getActionRegistration((string)$action_id, $action));
            if ($action->isAsync()) {
                $signal = clone $component->getAsyncActionSignal();
                $signal->addOption(Action::OPT_ACTIONID, $action_id);
                $action = $action->withSignalTarget($signal);
            }
            $actions[$action_id] = $action;
        }
        $component = $component->withActions($actions);

        if ($component->hasMultiActions()) {
            $component = $component->withAdditionalOnLoadCode(
                static fn($id): string => "il.UI.table.data.get('{$id}').selectAll(false);"
            );
        }

        //TODO: Filter
        $filter_data = [];
        $additional_parameters = [];
        [$component, $view_controls] = $component->applyViewControls(
            $filter_data = [],
            $additional_parameters = []
        );

        $tpl->setVariable('VIEW_CONTROLS', $default_renderer->render($view_controls));

        $rows = $component->getDataRetrieval()->getRows(
            $component->getRowBuilder(),
            array_keys($component->getVisibleColumns()),
            $component->getRange(),
            $component->getOrder(),
            $component->getFilter(),
            $component->getAdditionalParameters()
        );

        $id = $this->bindJavaScript($component);
        $tpl->setVariable('ID', $id);
        $tpl->setVariable('TITLE', $component->getTitle());
        $tpl->setVariable('COL_COUNT', (string) $component->getColumnCount());

        $sortation_signal = null;
        // if the generator is empty, and thus invalid, we render an empty row.
        if (!$rows->valid()) {
            $this->renderFullWidthDataCell($component, $tpl, $this->txt('ui_table_no_records'));
        } else {
            $this->appendTableRows($tpl, $rows, $default_renderer);

            if ($component->hasMultiActions()) {
                $multi_actions = $component->getMultiActions();
                $modal = $this->buildMultiActionsAllObjectsModal($multi_actions, $id);
                $multi_actions_dropdown = $this->buildMultiActionsDropdown(
                    $multi_actions,
                    $component->getMultiActionSignal(),
                    $modal->getShowSignal()
                );
                $tpl->setVariable('MULTI_ACTION_TRIGGERER', $default_renderer->render($multi_actions_dropdown));
                $tpl->setVariable('MULTI_ACTION_ALL_MODAL', $default_renderer->render($modal));
            }

            $sortation_signal = null;
            $sortation_view_control = array_filter(
                $view_controls->getInputs(),
                static fn($i): bool => $i instanceof Component\Input\ViewControl\Sortation
            );
            if($sortation_view_control) {
                $sortation_signal = array_shift($sortation_view_control)->getInternalSignal();
                $sortation_signal->addOption('parent_container', $id);
            }
        }

        $this->renderTableHeader($default_renderer, $component, $tpl, $sortation_signal);
        return $tpl->get();
    }

    protected function renderTableHeader(
        RendererInterface $default_renderer,
        Component\Table\Data $component,
        Template $tpl,
        ?Component\Signal $sortation_signal
    ): void {
        $order = $component->getOrder();
        $glyph_factory = $this->getUIFactory()->symbol()->glyph();
        $sort_col = key($order->get());
        $sort_direction = current($order->get());
        $columns = $component->getVisibleColumns();

        foreach ($columns as $col_id => $col) {
            $param_sort_direction = Order::ASC;
            $col_title = $col->getTitle();
            if ($col_id === $sort_col) {
                if ($sort_direction === Order::ASC) {
                    $sortation = $this->txt('order_option_generic_ascending');
                    $sortation_glyph = $glyph_factory->sortAscending("#");
                    $param_sort_direction = Order::DESC;
                }
                if ($sort_direction === Order::DESC) {
                    $sortation = $this->txt('order_option_generic_descending');
                    $sortation_glyph = $glyph_factory->sortDescending("#");
                }
            }

            $tpl->setCurrentBlock('header_cell');
            $tpl->setVariable('COL_INDEX', (string) $col->getIndex());

            if ($col->isSortable() && ! is_null($sortation_signal)) {
                $sort_signal = clone $sortation_signal;
                $sort_signal->addOption('value', "$col_id:$param_sort_direction");
                $col_title = $default_renderer->render(
                    $this->getUIFactory()->button()->shy($col_title, $sort_signal)
                );

                if ($col_id === $sort_col) {
                    $sortation_glyph = $default_renderer->render($sortation_glyph->withOnClick($sort_signal));
                    $tpl->setVariable('COL_SORTATION', $sortation);
                    $tpl->setVariable('COL_SORTATION_GLYPH', $sortation_glyph);
                }
            }

            $tpl->setVariable('COL_TITLE', $col_title);
            $tpl->setVariable('COL_TYPE', strtolower($col->getType()));
            $tpl->parseCurrentBlock();
        }

        if ($component->hasSingleActions()) {
            $tpl->setVariable('COL_INDEX_ACTION', (string) count($columns));
            $tpl->setVariable('COL_TITLE_ACTION', $this->txt('actions'));

        }

        if ($component->hasMultiActions()) {
            $signal = $component->getSelectionSignal();
            $sig_all = clone $signal;
            $sig_all->addOption('select', true);
            $select_all = $glyph_factory->add()->withOnClick($sig_all);
            $signal->addOption('select', false);
            $select_none = $glyph_factory->close()->withOnClick($signal);
            $tpl->setVariable('SELECTION_CONTROL_SELECT', $default_renderer->render($select_all));
            $tpl->setVariable('SELECTION_CONTROL_DESELECT', $default_renderer->render($select_none));
        }
    }

    /**
     * Renders a full-width cell with a single message within, indication there is no
     * data to display. This is achieved using a <td> colspan attribute.
     */
    protected function renderFullWidthDataCell(Component\Table\Data $component, Template $tpl, string $content): void
    {
        $cell_tpl = $this->getTemplate('tpl.datacell.html', true, true);
        $cell_tpl->setCurrentBlock('cell');
        $cell_tpl->setVariable('CELL_CONTENT', $content);
        $cell_tpl->setVariable('COL_SPAN', count($component->getVisibleColumns()));
        $cell_tpl->setVariable('COL_TYPE', 'full-width');
        $cell_tpl->setVariable('COL_INDEX', '1');
        $cell_tpl->parseCurrentBlock();

        $tpl->setCurrentBlock('row');
        $tpl->setVariable('ALTERNATION', 'even');
        $tpl->setVariable('CELLS', $cell_tpl->get());
        $tpl->parseCurrentBlock();
    }

    protected function appendTableRows(
        Template $tpl,
        \Generator $rows,
        RendererInterface $default_renderer
    ): void {
        $alternate = 'even';
        foreach ($rows as $row) {
            $row_contents = $default_renderer->render($row);
            $alternate = ($alternate === 'odd') ? 'even' : 'odd';
            $tpl->setCurrentBlock('row');
            $tpl->setVariable('ALTERNATION', $alternate);
            $tpl->setVariable('CELLS', $row_contents);
            $tpl->parseCurrentBlock();
        }
    }

    protected function renderEmptyPresentationRow(Template $tpl, RendererInterface $default_renderer, string $content): void
    {
        $row_tpl = $this->getTemplate('tpl.presentationrow_empty.html', true, true);
        $row_tpl->setVariable('CONTENT', $content);
        $tpl->setVariable('ROW', $row_tpl->get());
    }

    /**
     * @param array<string, Action> $actions
     */
    protected function buildMultiActionsAllObjectsModal(
        array $actions,
        string $table_id
    ): \ILIAS\UI\Component\Modal\RoundTrip {
        $f = $this->getUIFactory();

        $msg = $f->messageBox()->confirmation($this->txt('datatable_multiactionmodal_msg'));

        $select = $f->input()->field()->select(
            $this->txt('datatable_multiactionmodal_actionlabel'),
            array_map(
                static fn($action): string => $action->getLabel(),
                $actions
            ),
            ""
        );
        $submit = $f->button()->primary($this->txt('datatable_multiactionmodal_buttonlabel'), '')
            ->withOnLoadCode(
                static fn($id): string => "$('#{$id}').click(function() { il.UI.table.data.get('{$table_id}').doActionForAll(this); return false; });"
            );
        $modal = $f->modal()
            ->roundtrip($this->txt('datatable_multiactionmodal_title'), [$msg, $select])
            ->withActionButtons([$submit]);
        return $modal;
    }

    /**
     * @param array<string, Action> $actions
     */
    protected function buildMultiActionsDropdown(
        array $actions,
        Component\Signal $action_signal,
        Component\Signal $modal_signal,
    ): ?\ILIAS\UI\Component\Dropdown\Dropdown {
        if ($actions === []) {
            return null;
        }
        $f = $this->getUIFactory();
        $glyph = $f->symbol()->glyph()->bulletlist();
        $buttons = [];
        $all_obj_buttons = [];
        foreach ($actions as $action_id => $act) {
            $signal = clone $action_signal;
            $signal->addOption(Action::OPT_ACTIONID, $action_id);
            $buttons[] = $f->button()->shy($act->getLabel(), $signal);
        }

        $buttons[] = $f->divider()->horizontal();
        $buttons[] = $f->button()->shy($this->txt('datatable_multiactionmodal_listentry'), '#')->withOnClick($modal_signal);

        return $f->dropdown()->standard($buttons);
    }

    protected function getAsyncActionHandler(Component\Signal $action_signal): \Closure
    {
        return static function ($id) use ($action_signal): string {
            return "
                $(document).on('{$action_signal}', function(event, signal_data) {
                    il.UI.table.data.get('{$id}').doSingleAction(signal_data);
                    return false;
                });";
        };
    }
    protected function getMultiActionHandler(Component\Signal $action_signal): \Closure
    {
        return static function ($id) use ($action_signal): string {
            return "
                $(document).on('{$action_signal}', function(event, signal_data) {
                    il.UI.table.data.get('{$id}').doMultiAction(signal_data);
                    return false;
                });";
        };
    }

    protected function getSelectionHandler(Component\Signal $selection_signal): \Closure
    {
        return static function ($id) use ($selection_signal): string {
            return "
                $(document).on('{$selection_signal}', function(event, signal_data) {
                    il.UI.table.data.get('{$id}').selectAll(signal_data.options.select);
                    return false;
                });
            ";
        };
    }

    protected function getActionRegistration(
        string $action_id,
        Action $action
    ): \Closure {
        $async = $action->isAsync() ? 'true' : 'false';
        $url_builder_js = $action->getURLBuilderJS();
        $tokens_js = $action->getURLBuilderTokensJS();

        return static function ($id) use ($action_id, $async, $url_builder_js, $tokens_js): string {
            return "
                il.UI.table.data.get('{$id}').registerAction('{$action_id}', {$async}, {$url_builder_js}, {$tokens_js});
            ";
        };
    }

    public function renderDataRow(Component\Table\DataRow $component, RendererInterface $default_renderer): string
    {
        $cell_tpl = $this->getTemplate("tpl.datacell.html", true, true);
        $cols = $component->getColumns();

        foreach ($cols as $col_id => $column) {
            if ($column->isHighlighted()) {
                $cell_tpl->touchBlock('highlighted');
            }
            $cell_tpl->setCurrentBlock('cell');
            $cell_tpl->setVariable('COL_TYPE', strtolower($column->getType()));
            $cell_tpl->setVariable('COL_INDEX', $column->getIndex());
            $cell_content = $component->getCellContent($col_id);
            if ($cell_content instanceof Component\Component) {
                $cell_content = $default_renderer->render($cell_content);
            }
            $cell_tpl->setVariable('CELL_CONTENT', $cell_content);
            $cell_tpl->setVariable('CELL_COL_TITLE', $component->getColumns()[$col_id]->getTitle());
            $cell_tpl->parseCurrentBlock();
        }

        if ($component->tableHasMultiActions()) {
            $cell_tpl->setVariable('ROW_ID', $component->getId());
        }
        if ($component->tableHasSingleActions()) {
            $row_actions_dropdown = $this->getSingleActionsForRow(
                $component->getId(),
                $component->getActions()
            );
            $cell_tpl->setVariable('ACTION_CONTENT', $default_renderer->render($row_actions_dropdown));
        }

        return $cell_tpl->get();
    }

    /**
     * @param array<string, Action> $actions
     */
    protected function getSingleActionsForRow(string $row_id, array $actions): \ILIAS\UI\Component\Dropdown\Standard
    {
        $f = $this->getUIFactory();
        $buttons = [];
        foreach ($actions as $act) {
            $act = $act->withRowId($row_id);
            $target = $act->getTarget();
            if ($target instanceof URI) {
                $target = (string) $target;
            }
            $buttons[] = $f->button()->shy($act->getLabel(), $target);
        }
        return $f->dropdown()->standard($buttons);
    }

    /**
     * @inheritdoc
     */
    public function registerResources(ResourceRegistry $registry): void
    {
        parent::registerResources($registry);
        $registry->register('assets/js/table.min.js');
        $registry->register('assets/js/modal.js');
    }

    protected function registerSignals(Component\Table\PresentationRow $component): Component\JavaScriptBindable
    {
        $show = $component->getShowSignal();
        $close = $component->getCloseSignal();
        $toggle = $component->getToggleSignal();
        $table_id = $component->getTableId();
        return $component->withAdditionalOnLoadCode(
            static fn($id): string =>
            "$(document).on('$show', function() { il.UI.table.presentation.get('$table_id').expandRow('$id'); return false; });" .
            "$(document).on('$close', function() { il.UI.table.presentation.get('$table_id').collapseRow('$id'); return false; });" .
            "$(document).on('$toggle', function() { il.UI.table.presentation.get('$table_id').toggleRow('$id'); return false; });"
        );
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName(): array
    {
        return [
            Component\Table\PresentationRow::class,
            Component\Table\Presentation::class,
            Component\Table\Data::class,
            Component\Table\DataRow::class
        ];
    }
}
