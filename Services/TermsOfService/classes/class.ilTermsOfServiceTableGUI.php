<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Michael Jansen <mjansen@databay.de>
 */
abstract class ilTermsOfServiceTableGUI extends \ilTable2GUI
{
    /** @var \ilTermsOfServiceTableDataProvider */
    protected $provider;

    /** @var array */
    protected $visibleOptionalColumns = [];

    /** @var array */
    protected $optionalColumns = [];

    /** @var array */
    protected $filter = [];

    /** @var array */
    protected $optional_filter = [];

    /**
     * @inheritdoc
     */
    public function __construct($a_parent_obj, $command = '', $a_template_context = '')
    {
        parent::__construct($a_parent_obj, $command, $a_template_context);

        $columns = $this->getColumnDefinition();
        $this->optionalColumns = (array) $this->getSelectableColumns();
        $this->visibleOptionalColumns = (array) $this->getSelectedColumns();

        foreach ($columns as $index => $column) {
            if ($this->isColumnVisible($index)) {
                $this->addColumn(
                    $column['txt'],
                    isset($column['sortable']) && $column['sortable'] ? $column['field'] : '',
                    isset($column['width']) ? $column['width'] : '',
                    isset($column['is_checkbox']) ? (bool) $column['is_checkbox'] : false
                );
            }
        }
    }

    /**
     * @param \ilTermsOfServiceTableDataProvider $provider
     */
    public function setProvider(\ilTermsOfServiceTableDataProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @return \ilTermsOfServiceTableDataProvider
     */
    public function getProvider() : \ilTermsOfServiceTableDataProvider
    {
        return $this->provider;
    }

    /**
     * @param array $params
     * @param array $filter
     */
    protected function onBeforeDataFetched(array &$params, array &$filter)
    {
    }

    /**
     * This method can be used to add some field values dynamically or manipulate existing values of the table row array
     * @param array $row
     */
    protected function prepareRow(array &$row)
    {
    }

    /**
     * @param array $data
     */
    protected function preProcessData(array &$data)
    {
    }

    /**
     * Define a final formatting for a cell value
     * @param string $column
     * @param array $row
     * @return string
     */
    protected function formatCellValue(string $column, array $row) : string
    {
        return trim($row[$column]);
    }

    /**
     * @return array
     */
    public function getSelectableColumns()
    {
        $optionalColumns = array_filter($this->getColumnDefinition(), function ($column) {
            return isset($column['optional']) && $column['optional'];
        });

        $columns = array();
        foreach ($optionalColumns as $index => $column) {
            $columns[$column['field']] = $column;
        }

        return $columns;
    }

    /**
     * @param int $index
     * @return bool
     */
    protected function isColumnVisible(int $index)
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

    /**
     * @param array $row
     */
    final protected function fillRow($row)
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

    /**
     * @return array
     */
    abstract protected function getColumnDefinition() : array;

    /**
     *
     */
    public function populate()
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
