<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 * @abstract
 */
abstract class ilTermsOfServiceTableGUI extends ilTable2GUI
{
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var array
	 */
	protected $visibleOptionalColumns = array();

	/**
	 * @var ilTermsOfServiceTableDataProvider
	 */
	protected $provider;

	/**
	 * @var array
	 */
	protected $optionalColumns = array();

	/**
	 * @var array
	 */
	protected $filter = array();

	/**
	 * @var array
	 */
	protected $optional_filter = array();

	/**
	 * Set the provider to be used for data retrieval.
	 * @params    ilTableDataProvider $mapper
	 */
	public function setProvider(ilTermsOfServiceTableDataProvider $provider)
	{
		$this->provider = $provider;
	}

	/**
	 * Get the registered provider instance
	 * @return ilTermsOfServiceTableDataProvider
	 */
	public function getProvider()
	{
		return $this->provider;
	}

	/**
	 * @param string $column
	 * @return bool
	 */
	protected function isColumnVisible($column)
	{
		if(array_key_exists($column, $this->optionalColumns) && !isset($this->visibleOptionalColumns[$column]))
		{
			return false;
		}

		return true;
	}

	/**
	 * This method can be used to prepare values for sorting (e.g. translations), to filter items etc.
	 * It is called before sorting and segmentation.
	 * @param array $data
	 * @return array
	 */
	protected function prepareData(array &$data)
	{
	}

	/**
	 * This method can be used to manipulate the data of a row after sorting and segmentation
	 * @param array $data
	 * @return array
	 */
	protected function prepareRow(array &$row)
	{
	}

	/**
	 * Define a final formatting for a cell value
	 * @param mixed  $column
	 * @param array  $row
	 * @return mixed
	 */
	protected function formatCellValue($column, array $row)
	{
		return $row[$column];
	}

	/**
	 * @param array $row
	 */
	final protected function fillRow(array $row)
	{
		$this->prepareRow($row);

		foreach($this->getStaticData() as $column)
		{
			$value = $this->formatCellValue($column, $row);
			$this->tpl->setVariable('VAL_' . strtoupper($column), $value);
		}

		foreach($this->optionalColumns as $index => $definition)
		{
			if(!$this->isColumnVisible($index))
			{
				continue;
			}

			$this->tpl->setCurrentBlock('optional_column');
			$value = $this->formatCellValue($index, $row);
			if((string)$value === '')
			{
				$this->tpl->touchBlock('optional_column');
			}
			else
			{
				$this->tpl->setVariable('OPTIONAL_COLUMN_VAL', $value);
			}

			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	 * Return an array of all static (always visible) data fields in a row.
	 * For each key there has to be a variable name VAL_<COLUMN_KEY> in your defined row template.
	 * Example:
	 *     return array('title', 'checkbox');
	 *     There have to be two template variables: VAL_TITLE and VAL_CHECKBOX
	 * @return array
	 * @abstract
	 */
	abstract protected function getStaticData();

	/**
	 * @throws ilException
	 */
	public function populate()
	{
		if(!$this->getExternalSegmentation() && $this->getExternalSorting())
		{
			$this->determineOffsetAndOrder(true);
		}
		else if($this->getExternalSegmentation() || $this->getExternalSorting())
		{
			$this->determineOffsetAndOrder();
		}

		$params = array();
		if($this->getExternalSegmentation())
		{
			$params['limit']  = $this->getLimit();
			$params['offset'] = $this->getOffset();
		}
		if($this->getExternalSorting())
		{
			$params['order_field']     = $this->getOrderField();
			$params['order_direction'] = $this->getOrderDirection();
		}

		$this->determineSelectedFilters();
		$filter = $this->filter;
		
		foreach($this->optional_filter as $key => $value)
		{
			if($this->isFilterSelected($key))
			{
				$filter[$key] = $value;
			}
		}

		$data = $this->getProvider()->getList($params, $filter);

		if(!count($data['items']) && $this->getOffset() > 0 && $this->getExternalSegmentation())
		{
			$this->resetOffset();
			$params['limit']  = $this->getLimit();
			$params['offset'] = $this->getOffset();
			$data             = $this->getProvider()->getList($params, $filter);
		}

		$this->prepareData($data);

		$this->setData($data['items']);
		if($this->getExternalSegmentation())
		{
			$this->setMaxCount($data['cnt']);
		}
	}
}
