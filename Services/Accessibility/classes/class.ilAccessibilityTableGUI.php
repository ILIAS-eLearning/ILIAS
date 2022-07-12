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

/**
 * Class ilAccessibilityTableGUI
 */
abstract class ilAccessibilityTableGUI extends ilTable2GUI
{
    protected ?ilAccessibilityTableDataProvider $provider;
    protected array $visibleOptionalColumns = [];
    protected array $optionalColumns = [];
    protected array $filter = [];
    protected array $optional_filter = [];

    public function __construct(
        object $a_parent_obj,
        string $command = '',
        string $a_template_context = ''
    ) {
        parent::__construct($a_parent_obj, $command, $a_template_context);

        $columns = $this->getColumnDefinition();
        $this->optionalColumns = (array) $this->getSelectableColumns();
        $this->visibleOptionalColumns = (array) $this->getSelectedColumns();

        foreach ($columns as $index => $column) {
            if ($this->isColumnVisible($index)) {
                $this->addColumn(
                    $column['txt'],
                    isset($column['sortable']) && $column['sortable'] ? $column['field'] : '',
                    $column['width'] ?? '',
                    isset($column['is_checkbox']) && (bool) $column['is_checkbox']
                );
            }
        }
    }

    public function setProvider(ilAccessibilityTableDataProvider $provider) : void
    {
        $this->provider = $provider;
    }

    public function getProvider() : ? ilAccessibilityTableDataProvider
    {
        return $this->provider;
    }

    protected function onBeforeDataFetched(array &$params, array &$filter) : void
    {
    }

    /**
     * This method can be used to add some field values dynamically or manipulate existing values of the table row array
     */
    protected function prepareRow(array &$row) : void
    {
    }

    protected function preProcessData(array &$data) : void
    {
    }

    /**
     * Define a final formatting for a cell value
     */
    protected function formatCellValue(string $column, array $row) : string
    {
        return trim($row[$column]);
    }

    public function getSelectableColumns() : array
    {
        $optionalColumns = array_filter($this->getColumnDefinition(), function ($column) {
            return isset($column['optional']) && $column['optional'];
        });

        $columns = [];
        foreach ($optionalColumns as $index => $column) {
            $columns[$column['field']] = $column;
        }

        return $columns;
    }

    protected function isColumnVisible(int $index) : bool
    {
        $columnDefinition = $this->getColumnDefinition();
        if (array_key_exists($index, $columnDefinition)) {
            $column = $columnDefinition[$index];
            if (isset($column['optional']) && !$column['optional']) {
                return true;
            }

            if (
                is_array($this->visibleOptionalColumns) &&
                array_key_exists($column['field'], $this->visibleOptionalColumns)
            ) {
                return true;
            }
        }

        return false;
    }

    final protected function fillRow(array $row) : void
    {
        $this->prepareRow($row);

        foreach ($this->getColumnDefinition() as $index => $column) {
            if (!$this->isColumnVisible($index)) {
                continue;
            }

            $this->tpl->setCurrentBlock('column');
            $value = $this->formatCellValue($column['field'], $row);
            if ((string) $value === '') {
                $this->tpl->touchBlock('column');
            } else {
                $this->tpl->setVariable('COLUMN_VALUE', $value);
            }

            $this->tpl->parseCurrentBlock();
        }
    }

    abstract protected function getColumnDefinition() : array;

    public function populate() : void
    {
        if ($this->getExternalSegmentation() && $this->getExternalSorting()) {
            $this->determineOffsetAndOrder();
        } else {
            if (!$this->getExternalSegmentation() && $this->getExternalSorting()) {
                $this->determineOffsetAndOrder(true);
            }
        }

        $params = [];
        if ($this->getExternalSegmentation()) {
            $params['limit'] = $this->getLimit();
            $params['offset'] = $this->getOffset();
        }
        if ($this->getExternalSorting()) {
            $params['order_field'] = $this->getOrderField();
            $params['order_direction'] = $this->getOrderDirection();
        }

        $this->determineSelectedFilters();
        $filter = (array) $this->filter;

        foreach ($this->optional_filter as $key => $value) {
            if ($this->isFilterSelected($key)) {
                $filter[$key] = $value;
            }
        }

        $this->onBeforeDataFetched($params, $filter);
        $data = $this->getProvider()->getList($params, $filter);

        if (!count($data['items']) && $this->getOffset() > 0 && $this->getExternalSegmentation()) {
            $this->resetOffset();
            if ($this->getExternalSegmentation()) {
                $params['limit'] = $this->getLimit();
                $params['offset'] = $this->getOffset();
            }
            $data = $this->provider->getList($params, $filter);
        }

        $this->preProcessData($data);

        $this->setData($data['items']);
        if ($this->getExternalSegmentation()) {
            $this->setMaxCount($data['cnt']);
        }
    }
}
