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
        if ($component instanceof Component\Table\Row) {
            return $this->renderStandardRow($component, $default_renderer);
        }
    }

    protected function renderPresentationTable(
        Component\Table\Presentation $component,
        RendererInterface $default_renderer
    ): string {
        $tpl = $this->getTemplate("tpl.presentationtable.html", true, true);

        $tpl->setVariable("TITLE", $component->getTitle());

        $vcs = $component->getViewControls();
        if ($vcs) {
            $tpl->touchBlock("viewcontrols");
            foreach ($vcs as $vc) {
                $tpl->setCurrentBlock("vc");
                $tpl->setVariable("VC", $default_renderer->render($vc));
                $tpl->parseCurrentBlock();
            }
        }
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


    public function renderDataTable(Component\Table\Data $component, RendererInterface $default_renderer)
    {
        $tpl = $this->getTemplate("tpl.datatable.html", true, true);

        $f = $this->getUIFactory();
        $df = $this->getDataFactory();

        //TODO: VIEWCONTROLS
        $range = $df->range(0, 10);
        $order = $df->order('f1', \ILIAS\Data\Order::ASC);
        $selected_optional = array_keys($component->getColumns());
        //END TODO: VIEWCONTROLS

        $component = $component
            ->withSelectedOptionalColumns($selected_optional)
            ->withRange($range)
            ->withOrder($order);

        $data_retrieval = $component->getData();
        $row_factory = $component->getRowFactory();
        $columns = $component->getFilteredColumns();

        $additional_parameters = [];
        $rows = $data_retrieval->getRows(
            $component->getRowFactory(),
            $component->getRange(),
            $component->getOrder(),
            array_keys($columns),
            $additional_parameters
        );

        $component = $this->registerActionsJS($component);
        $id = $this->bindJavaScript($component);
        $tpl->setVariable('ID', $id);
        $tpl->setVariable('TITLE', $component->getTitle());
        $tpl->setVariable('COL_COUNT', (string) $component->getColumnCount());
        $tpl->setVariable('VIEW_CONTROLS', $default_renderer->render($component->getViewControls()));

        $this->renderTableHeader($default_renderer, $component->hasActions(), $tpl, $columns, $order);
        $this->appendTableRows($tpl, $rows, $default_renderer);

        $multi_actions_dropdown = $this->getMultiActionsDropdown(
            $component->getMultiActions(),
            $component->getActionSignal()
        );
        if ($multi_actions_dropdown) {
            $tpl->setVariable('MULTI_ACTION_TRIGGERER', $default_renderer->render($multi_actions_dropdown));
        }

        return $tpl->get();
    }

    protected function registerActionsJS(Component\Table\Data $component): Component\Table\Data
    {
        $component = $component->withAdditionalOnLoadCode(
            $this->getMultiActionHandler($component->getActionSignal())
        );
        $actions = $component->getActions();
        foreach ($actions as $action_id => $action) {
            $component = $component->withAdditionalOnLoadCode(
                $this->getActionRegistration($action_id, $action)
            );
        }
        return $component;
    }

    /**
     * @param Column\Column[]
     */
    protected function renderTableHeader(
        RendererInterface $default_renderer,
        bool $render_action_column,
        Template $tpl,
        array $columns,
        \ILIAS\Data\Order $order
    ) {
        $sort_col = key($order->get());
        $sort_direction = current($order->get());

        foreach ($columns as $col_id => $col) {
            if ($col_id === $sort_col) {
                if ($sort_direction === $order::ASC) {
                    $sortation = 'ascending';
                    $sortation_glyph = $this->getUIFactory()->symbol()->glyph()->sortAscending("#");
                }
                if ($sort_direction === $order::DESC) {
                    $sortation = 'decending';
                    $sortation_glyph = $this->getUIFactory()->symbol()->glyph()->sortDescending("#");
                }
                $sortation_glyph = $default_renderer->render($sortation_glyph->withUnavailableAction());
                $tpl->setVariable('COL_SORTATION', $sortation);
                $tpl->setVariable('COL_SORTATION_GLYPH', $sortation_glyph);
            }

            $tpl->setCurrentBlock('header_cell');
            $tpl->setVariable('COL_INDEX', (string) $col->getIndex());
            $tpl->setVariable('COL_TITLE', $col->getTitle());
            $tpl->setVariable('COL_TYPE', strtolower($col->getType()));
            $tpl->parseCurrentBlock();
        }

        if ($render_action_column) {
            $tpl->touchBlock('header_rowselection_cell');
            $tpl->touchBlock('header_action_cell');
        }
    }

    /**
     * @param Row[] $rows
     */
    protected function appendTableRows(
        Template $tpl,
        \Generator $rows,
        RendererInterface $default_renderer
    ) {
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

    protected function getMultiActionsDropdown(
        array $actions,
        Component\Signal $action_signal
    ): ?\ILIAS\UI\Component\dropdown\Dropdown {
        if (count($actions) < 1) {
            return null;
        }
        $f = $this->getUIFactory();
        $buttons = [];

        foreach ($actions as $action_id => $act) {
            $signal = clone $action_signal;
            $signal->addOption('action', $action_id);
            $buttons[] = $f->button()->shy($act->getLabel(), $signal);
        }
        $dropdown = $f->dropdown()->standard($buttons);
        return $dropdown;
    }

    protected function getMultiActionHandler(Component\Signal $action_signal): \Closure
    {
        return function ($id) use ($action_signal) {
            return "
                $(document).on('{$action_signal}', function(event, signal_data) {
                    il.UI.table.data.doAction('{$id}', signal_data, il.UI.table.data.collectSelectedRowIds('{$id}'));
                    return false;
                });";
        };
    }

    protected function getActionRegistration(
        string $action_id,
        Component\Table\Action\Action $action
    ): \Closure {
        $parameter_name = $action->getParameterName();
        $target = $action->getTarget();
        $type = 'URL';

        if ($target instanceof Component\Signal) {
            $type = 'SIGNAL';
            $target = json_encode([
                'id' => $target->getId(),
                'options' => $target->getOptions()
            ]);
        }

        return function ($id) use ($action_id, $type, $target, $parameter_name) {
            return "
                il.UI.table.data.registerAction('{$id}', '{$action_id}', '{$type}', '{$target}', '{$parameter_name}');
            ";
        };
    }

    public function renderStandardRow(Component\Table\Row $component, RendererInterface $default_renderer)
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


        if ($component->tableHasActions()) {
            $cell_tpl->setVariable('ROW_ID', $component->getId());

            $row_actions_dropdown = $this->getSingleActionsForRow(
                $component->getId(),
                $component->getActions()
            );
            $cell_tpl->setVariable('ACTION_CONTENT', $default_renderer->render($row_actions_dropdown));
        }

        return $cell_tpl->get();
    }

    protected function getSingleActionsForRow(string $row_id, array $actions): ?\ILIAS\UI\Component\Dropdown\Dropdown
    {
        $f = $this->getUIFactory();
        $buttons = [];
        foreach ($actions as $act_id => $act) {
            $act = $act->withRowId($row_id);
            $target = $act->getTarget();
            if (!$target instanceof Component\Signal) {
                $target = (string) $target;
            }
            $buttons[] = $f->button()->shy($act->getLabel(), $target);
        }
        $dropdown = $f->dropdown()->standard($buttons); //TODO (maybe?) ->withLabel("Actions")
        return $dropdown;
    }

    /**
     * @inheritdoc
     */
    public function registerResources(ResourceRegistry $registry): void
    {
        parent::registerResources($registry);
        $registry->register('./src/UI/templates/js/Table/presentation.js');
        $registry->register('./src/UI/templates/js/Table/dist/table.js');
    }

    protected function registerSignals(Component\Table\PresentationRow $component): Component\JavaScriptBindable
    {
        $show = $component->getShowSignal();
        $close = $component->getCloseSignal();
        $toggle = $component->getToggleSignal();
        return $component->withAdditionalOnLoadCode(fn ($id) => "$(document).on('$show', function() { il.UI.table.presentation.expandRow('$id'); return false; });" .
        "$(document).on('$close', function() { il.UI.table.presentation.collapseRow('$id'); return false; });" .
        "$(document).on('$toggle', function() { il.UI.table.presentation.toggleRow('$id'); return false; });");
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName(): array
    {
        return array(
            Component\Table\PresentationRow::class,
            Component\Table\Presentation::class,
            Component\Table\Data::class,
            Component\Table\Row::class
        );
    }
}
