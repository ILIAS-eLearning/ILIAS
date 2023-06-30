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
use ILIAS\UI\Implementation\Component\Table\Action\Action;

class Renderer extends AbstractComponentRenderer
{
    /**
     * The parameters for ordering will be removed as soon as ViewControls
     * are available for the Table. DO NOT USE THEM.
     * This is purely to demonstrate column-ordering already.
     */
    private const TABLE_DEMO_ORDER_FIELD = 'tsort_f';
    private const TABLE_DEMO_ORDER_DIRECTION = 'tsort_d';

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


        $vcs = [];
        if ($sig_ta = $component->getToggleAllSignal()) {
            $sig_ta_expand = clone $sig_ta;
            $sig_ta_expand->addOption('expand', true);
            $vcs[] = $this->getUIFactory()->symbol()->glyph()
                ->expand()->withOnClick($sig_ta_expand);
            $sig_ta_collapse = clone $sig_ta;
            $sig_ta_collapse->addOption('expand', false);
            $vcs[] = $this->getUIFactory()->symbol()->glyph()
                ->collapse()->withOnClick($sig_ta_collapse);

            $component = $component->withAdditionalOnLoadCode(
                fn($id) => "$(document).on('$sig_ta', function(event, signal_data) { il.UI.table.presentation.expandAll('$id', signal_data); return false; });"
            );
        }
        $vcs = array_merge($vcs, $component->getViewControls());

        if ($vcs) {
            $tpl->touchBlock("viewcontrols");
            foreach ($vcs as $vc) {
                $tpl->setCurrentBlock("vc");
                $tpl->setVariable("VC", $default_renderer->render($vc));
                $tpl->parseCurrentBlock();
            }
        }

        $id = $this->bindJavaScript($component);
        $tpl->setVariable("ID", $id);

        $row_mapping = $component->getRowMapping();
        $data = $component->getData();

        foreach ($data as $record) {
            $row = $row_mapping(
                new PresentationRow($component->getSignalGenerator()),
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

        $component = $this->applyViewControls($component);

        list($type_url, $type_sig, $opt_opt, $opt_id) = [Action::TYPE_URL, Action::TYPE_SIGNAL, Action::OPT_OPTIONS, Action::OPT_ID];
        $component = $component
            ->withAdditionalOnLoadCode(
                static fn($id): string =>
                    "il.UI.table.data.init('{$id}','{$type_url}', '{$type_sig}', '{$opt_opt}', '{$opt_id}' );"
            )
            ->withAdditionalOnLoadCode($this->getMultiActionHandler($component->getActionSignal()))
            ->withAdditionalOnLoadCode($this->getSelectionHandler($component->getSelectionSignal()));

        foreach ($component->getAllActions() as $action_id => $action) {
            $component = $component->withAdditionalOnLoadCode($this->getActionRegistration($action_id, $action));
        }
        if ($component->hasMultiActions()) {
            $component = $component->withAdditionalOnLoadCode(
                static fn($id): string => "il.UI.table.data.get('{$id}').selectAll(false);"
            );
        }

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
        $tpl->setVariable('VIEW_CONTROLS', $default_renderer->render($component->getViewControls()));

        $this->renderTableHeader($default_renderer, $component, $tpl);
        $this->appendTableRows($tpl, $rows, $default_renderer);

        if ($component->hasMultiActions()) {
            $multi_actions = $component->getMultiActions();
            $modal = $this->buildMultiActionsAllObjectsModal($multi_actions, $id);
            $multi_actions_dropdown = $this->buildMultiActionsDropdown(
                $multi_actions,
                $component->getActionSignal(),
                $modal->getShowSignal()
            );
            $tpl->setVariable('MULTI_ACTION_TRIGGERER', $default_renderer->render($multi_actions_dropdown));
            $tpl->setVariable('MULTI_ACTION_ALL_MODAL', $default_renderer->render($modal));
        }

        return $tpl->get();
    }


    protected function applyViewControls(Component\Table\Data $component): Component\Table\Data
    {
        //TODO: Viewcontrols, Filter
        $df = $this->getDataFactory();
        $range = $component->getRange();
        $order = $component->getOrder();
        $selected_optional = $component->getSelectedOptionalColumns();
        $filter_data = null;
        $additional_parameters = null;

        if ($request = $component->getRequest()) {
            $params = [];
            parse_str($request->getUri()->getQuery(), $params);
            if (array_key_exists(self::TABLE_DEMO_ORDER_FIELD, $params) && array_key_exists(self::TABLE_DEMO_ORDER_DIRECTION, $params)
                && array_key_exists($params[self::TABLE_DEMO_ORDER_FIELD], $component->getVisibleColumns())
            ) {
                $order = $df->order($params[self::TABLE_DEMO_ORDER_FIELD], $params[self::TABLE_DEMO_ORDER_DIRECTION]);
            }
        }
        //END TODO: Viewcontrols, Filter

        return $component
            ->withSelectedOptionalColumns($selected_optional)
            ->withRange($range)
            ->withOrder($order)
            ->withFilter($filter_data)
            ->withAdditionalParameters($additional_parameters);
    }

    protected function renderTableHeader(
        RendererInterface $default_renderer,
        Component\Table\Data $component,
        Template $tpl
    ): void {
        $order = $component->getOrder();
        $glyph_factory = $this->getUIFactory()->symbol()->glyph();
        $sort_col = key($order->get());
        $sort_direction = current($order->get());
        $columns = $component->getVisibleColumns();

        $request = $component->getRequest();

        foreach ($columns as $col_id => $col) {
            $param_sort_direction = Order::ASC;
            $col_title = $col->getTitle();
            if ($col_id === $sort_col) {
                if ($sort_direction === Order::ASC) {
                    $sortation = 'ascending';
                    $sortation_glyph = $glyph_factory->sortAscending("#");
                    $param_sort_direction = Order::DESC;
                }
                if ($sort_direction === Order::DESC) {
                    $sortation = 'decending';
                    $sortation_glyph = $glyph_factory->sortDescending("#");
                }
            }

            $tpl->setCurrentBlock('header_cell');
            $tpl->setVariable('COL_INDEX', (string) $col->getIndex());

            if ($col->isSortable() && ! is_null($request)) {
                $uri = (string)$this->getDataFactory()->uri($request->getUri()->__toString())
                    ->withParameter(self::TABLE_DEMO_ORDER_FIELD, $col_id)
                    ->withParameter(self::TABLE_DEMO_ORDER_DIRECTION, $param_sort_direction);

                $col_title = $default_renderer->render(
                    $this->getUIFactory()->button()->shy($col_title, $uri)
                );

                if ($col_id === $sort_col) {
                    $sortation_glyph = $default_renderer->render($sortation_glyph->withAction($uri));
                    $tpl->setVariable('COL_SORTATION', $sortation);
                    $tpl->setVariable('COL_SORTATION_GLYPH', $sortation_glyph);
                }
            }

            $tpl->setVariable('COL_TITLE', $col_title);
            $tpl->setVariable('COL_TYPE', strtolower($col->getType()));
            $tpl->parseCurrentBlock();
        }

        if ($component->hasSingleActions()) {
            $tpl->touchBlock('header_action_cell');
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
            $signal->addOption('action', $action_id);
            $buttons[] = $f->button()->shy($act->getLabel(), $signal);
        }

        $buttons[] = $f->divider()->horizontal();
        $buttons[] = $f->button()->shy($this->txt('datatable_multiactionmodal_listentry'), '#')->withOnClick($modal_signal);

        return $f->dropdown()->standard($buttons);
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
        $parameter_name = $action->getParameterName();
        $target = $action->getTarget();
        $type = Action::TYPE_URL;

        if ($target instanceof Component\Signal) {
            $type = Action::TYPE_SIGNAL;
            $target = json_encode([
                Action::OPT_ID => $target->getId(),
                Action::OPT_OPTIONS => $target->getOptions()
            ]);
        }

        return static function ($id) use ($action_id, $type, $target, $parameter_name): string {
            return "
                il.UI.table.data.get('{$id}').registerAction('{$action_id}', '{$type}', '{$target}', '{$parameter_name}');
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
            $cell_tpl->setVariable('CELL_CONTENT', $component->getCellContent($col_id));
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
            if (!$target instanceof Component\Signal) {
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
        $registry->register('./src/UI/templates/js/Table/presentation.js');
        $registry->register('./src/UI/templates/js/Table/dist/datatable.js');
    }

    protected function registerSignals(Component\Table\PresentationRow $component): Component\JavaScriptBindable
    {
        $show = $component->getShowSignal();
        $close = $component->getCloseSignal();
        $toggle = $component->getToggleSignal();
        return $component->withAdditionalOnLoadCode(
            static fn($id): string =>
            "$(document).on('$show', function() { il.UI.table.presentation.expandRow('$id'); return false; });" .
            "$(document).on('$close', function() { il.UI.table.presentation.collapseRow('$id'); return false; });" .
            "$(document).on('$toggle', function() { il.UI.table.presentation.toggleRow('$id'); return false; });"
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
