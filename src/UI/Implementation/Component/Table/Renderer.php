<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts.and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Table;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Render\ilTemplateWrapper as Template;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer)
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

    /**
     * @param Component\Table\Presentation $component
     * @param RendererInterface $default_renderer
     * @return mixed
     */
    protected function renderPresentationTable(Component\Table\Presentation $component, RendererInterface $default_renderer)
    {
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

    /**
     * @param Component\Table\Presentation $component
     * @param RendererInterface $default_renderer
     * @return mixed
     */
    protected function renderPresentationRow(Component\Table\PresentationRow $component, RendererInterface $default_renderer)
    {
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
        $tpl->setVariable("SUBHEADLINE", $component->getSubheadline());

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
        if ($further_fields_headline) {
            $tpl->setVariable("FURTHER_FIELDS_HEADLINE", $further_fields_headline);
        }

        foreach ($component->getFurtherFields() as $label => $value) {
            $tpl->setCurrentBlock("further_field");
            if (is_string($label)) {
                $tpl->setVariable("FIELD_LABEL", $label);
            }
            $tpl->setVariable("FIELD_VALUE", $value);
            $tpl->parseCurrentBlock();
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
        $f = $this->getUIFactory();
        $df = new \ILIAS\Data\Factory();
        //TODO: fix/implement the following (and move it to the component....)
        $range = $df->range(0, 10);
        $order = $df->order('f1', \ILIAS\Data\Order::ASC);
        $visible_col_ids = [];
        $additional_parameters = [];

        $row_factory = $component->getRowFactory();
        $data_retrieval = $component->getData();
        $columns = $component->getFilteredColumns();

        $rows = $data_retrieval->getRows(
            $row_factory,
            $range,
            $order,
            array_keys($columns),
            $additional_parameters
        );

        $tpl = $this->getTemplate("tpl.datatable.html", true, true);

        $component = $this->registerActionsForTable($component);
        $id = $this->bindJavaScript($component);

        $tpl->setVariable('ID', $id);
        $tpl->setVariable('TITLE', $title);
        $tpl->setVariable('COL_COUNT', (string) $overall_column_count);

        $this->renderTableHeader($tpl, $columns);
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


    protected function getMultiActionsDropdown(
        array $actions,
        Component\Signal $action_signal
    ) : ?\ILIAS\UI\Component\dropdown\Dropdown {
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

    /**
     * @param Column\Column[]
     */
    protected function renderTableHeader(Template $tpl, array $columns)
    {
        foreach ($columns as $col_id => $col) {
            $sortation = 'none';
            $tpl->setCurrentBlock('header_cell');
            $tpl->setVariable('COL_INDEX', (string) $col->getIndex());
            $tpl->setVariable('COL_SORTATION', $sortation);
            $tpl->setVariable('COL_TITLE', $col->getTitle());
            $tpl->parseCurrentBlock();
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

    protected function registerActionsForTable(Component\Table\Data $component) : Component\Table\Data
    {
        //register handler
        $action_signal = $component->getActionSignal();
        $component = $component->withAdditionalOnLoadCode(function ($id) use ($action_signal) {
            return "
                $(document).on('{$action_signal}', function(event, signal_data) {
                    il.UI.table.data.doAction('{$id}', signal_data, il.UI.table.data.collectSelectedRowIds('{$id}'));
                    return false;
                });";
        });

        //register actions
        $actions = $component->getActions();
        foreach ($actions as $action_id => $act) {
            $parameter_name = $act->getParameterName();
            $target = $act->getTarget();
            $type = 'URL';

            if ($target instanceof Component\Signal) {
                $type = 'SIGNAL';
                $target = json_encode([
                    'id' => $target->getId(),
                    'options' => $target->getOptions()
                ]);
            }

            $component = $component->withAdditionalOnLoadCode(function ($id) use ($action_id, $type, $target, $parameter_name) {
                return "
                    il.UI.table.data.registerAction('{$id}', '{$action_id}', '{$type}', '{$target}', '{$parameter_name}');
                ";
            });
        }
        return $component;
    }


    public function renderStandardRow(Component\Table\Row $component, RendererInterface $default_renderer)
    {
        $cell_tpl = $this->getTemplate("tpl.datacell.html", true, true);
        $cols = $component->getColumns();

        foreach ($cols as $col_id => $column) {
            $cell_tpl->setCurrentBlock('cell');
            $cell_tpl->setVariable('COL_TYPE', $column->getType());
            $cell_tpl->setVariable('COL_INDEX', $column->getIndex());
            $cell_tpl->setVariable('CELL_CONTENT', $component->getCellContent($col_id));
            $cell_tpl->parseCurrentBlock();
        }

        if ($component->isSelectable()) {
            $cell_tpl->setVariable('ROW_ID', $component->getId());
        }

        $row_actions_html = $this->renderSingleActionsForRow(
            $component->getId(),
            $component->getActions(),
            $default_renderer
        );
        $cell_tpl->setVariable('ACTION_CONTENT', $row_actions_html);

        return $cell_tpl->get();
    }


    protected function renderSingleActionsForRow(
        string $row_id,
        array $actions,
        RendererInterface $default_renderer
    ) : string {
        $f = $this->getUIFactory();
        $buttons = [];
        foreach ($actions as $act_id => $act) {
            $act = $act->withRowId($row_id);
            $buttons[] = $f->button()->shy(
                $act->getLabel(),
                $act->getTargetForButton()
            );
        }
        $dropdown = $f->dropdown()->standard($buttons); //TODO (maybe?) ->withLabel("Actions")
        return $default_renderer->render($dropdown);
    }

    /**
     * @inheritdoc
     */
    public function registerResources(ResourceRegistry $registry)
    {
        parent::registerResources($registry);
        $registry->register('./src/UI/templates/js/Table/presentation.js');
        $registry->register('./src/UI/templates/js/Table/dist/table.js');
    }

    /**
     * @param Component\Table\PresentationRow $component
     */
    protected function registerSignals(Component\Table\PresentationRow $component)
    {
        $show = $component->getShowSignal();
        $close = $component->getCloseSignal();
        $toggle = $component->getToggleSignal();
        return $component->withAdditionalOnLoadCode(function ($id) use ($show, $close, $toggle) {
            return
                "$(document).on('{$show}', function() { il.UI.table.presentation.expandRow('{$id}'); return false; });" .
                "$(document).on('{$close}', function() { il.UI.table.presentation.collapseRow('{$id}'); return false; });" .
                "$(document).on('{$toggle}', function() { il.UI.table.presentation.toggleRow('{$id}'); return false; });";
        });
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName()
    {
        return array(
            Component\Table\PresentationRow::class,
            Component\Table\Presentation::class,
            Component\Table\Data::class,
            Component\Table\Row::class
        );
    }
}
