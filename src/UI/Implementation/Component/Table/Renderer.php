<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts.and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Table;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

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
        //TODO: fix/implement the following
        $range = $df->range(0, 10);
        $order = $df->order('f1', \ILIAS\Data\Order::ASC);
        $visible_col_ids = [];
        $sortation = 'none';
        $additional_parameters = [];

        $columns = $component->getColumns();
        $visible_col_ids = array_keys($columns);
        $filtered_columns = array_filter(
            $columns,
            function ($col_id) use ($visible_col_ids) {
                return in_array($col_id, $visible_col_ids);
            },
            ARRAY_FILTER_USE_KEY
        );

        $component = $this->registerActionsForTable($component);
        $multi_actions_dropdown = $this->getMultiActionDropdown($component);

        $tpl = $this->getTemplate("tpl.datatable.html", true, true);
        $tpl->setVariable('TITLE', $component->getTitle());
        $tpl->setVariable('COL_COUNT', count($columns)); //TODO: or filtered?!
        if ($multi_actions_dropdown) {
            $tpl->setVariable('MULTI_ACTION_TRIGGERER', $default_renderer->render($multi_actions_dropdown));
        }
        $tpl->setVariable('ID', $this->bindJavaScript($component));

        foreach ($columns as $col_id => $col) {
            $tpl->setCurrentBlock('header_cell');
            $tpl->setVariable('COL_INDEX', (string) $col->getIndex());
            $tpl->setVariable('COL_SORTATION', $sortation);
            $tpl->setVariable('COL_TITLE', $col->getTitle());
            $tpl->parseCurrentBlock();
        }

        $row_factory = new RowFactory($filtered_columns, $component->getActions());
        $alternate = 'odd';
        foreach ($component->getData()->getRows(
            $row_factory,
            $range,
            $order,
            array_keys($filtered_columns),
            $additional_parameters
        ) as $row) {
            $tpl->setCurrentBlock('row');
            $tpl->setVariable('ALTERNATION', $alternate);
            $tpl->setVariable('CELLS', $default_renderer->render($row));
            $tpl->parseCurrentBlock();
            $alternate = ($alternate === 'odd') ? 'even' : 'odd';
        }

        return $tpl->get();
    }


    protected function getFilteredActions(array $actions, string $exclude) : array
    {
        return array_filter(
            $actions,
            function ($action) use ($exclude) {
                return !is_a($action, $exclude);
            }
        );
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

            if ($target instanceof \ILIAS\Data\URI) {
                $type = 'URL';
                parse_str($target->getQuery(), $params);
                $target = $target->getBaseURI() . '?' . http_build_query($params);
            }
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

    protected function getMultiActionDropdown(Component\Table\Data $component) : ?Component\Dropdown\Dropdown
    {
        $f = $this->getUIFactory();
        $actions = $component->getActions();
        $multi_actions = $this->getFilteredActions(
            $actions,
            Component\Table\Action\Single::class
        );
        if (count($multi_actions) == 0) {
            return null;
        }
        $buttons = [];
        foreach ($multi_actions as $action_id => $act) {
            $buttons[] = $f->button()->shy(
                $act->getLabel(),
                $component->getActionSignal($action_id)
            );
        }
        $multi_actions_dropdown = $f->dropdown()->standard($buttons); //TODO ->withLabel("Actions")
        return $multi_actions_dropdown;
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

        $actions = $component->getActions();

        $multi_actions = $this->getFilteredActions($actions, Component\Table\Action\Single::class);
        $has_multi_actions = count($multi_actions) > 0;
        if ($has_multi_actions) {
            $cell_tpl->setVariable('ROW_ID', $component->getId());
        }

        $single_actions = $this->getFilteredActions($actions, Component\Table\Action\Multi::class);
        $single_actions_html = $this->renderSingleActionsForRow(
            $component->getId(),
            array_values($single_actions),
            $default_renderer
        );
        $cell_tpl->setVariable('ACTION_CONTENT', $single_actions_html);

        return $cell_tpl->get();
    }


    protected function renderSingleActionsForRow(
        string $row_id,
        array $actions,
        RendererInterface $default_renderer
    ) : string {
        if (count($actions) < 1) {
            return '';
        }

        $f = $this->getUIFactory();
        if (count($actions) === 1) { //TODO: do we actually want that switch or stick with the DD for conformity?
            $act = array_shift($actions);
            $triggerer = $f->button()->standard(
                $act->getLabel(),
                $this->amendParameters($act, $row_id)
            );
        }
        if (count($actions) > 1) {
            $buttons = [];
            foreach ($actions as $act) {
                $buttons[] = $f->button()->shy(
                    $act->getLabel(),
                    $this->amendParameters($act, $row_id)
                );
            }
            $triggerer = $f->dropdown()->standard($buttons); //TODO (maybe?) ->withLabel("Actions")
        }
        return $default_renderer->render($triggerer);
    }

    protected function amendParameters(Action\Action $action, string $row_id)
    {
        $target = $action->getTarget();
        $param = $action->getParameterName();
        if ($target instanceof Component\Signal) {
            $target->addOption($param, $row_id);
            return $target;
        }
        if ($target instanceof \ILIAS\Data\URI) {
            parse_str($target->getQuery(), $params);
            $params[$param] = $row_id;
            return $target->getBaseURI() . '?' . http_build_query($params);
        }
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
