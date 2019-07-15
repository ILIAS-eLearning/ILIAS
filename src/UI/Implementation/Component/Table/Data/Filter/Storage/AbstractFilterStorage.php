<?php

namespace ILIAS\UI\Implementation\Component\Table\Data\Filter\Storage;

use ILIAS\UI\Component\Table\Data\Column\Column;
use ILIAS\UI\Component\Table\Data\Filter\Filter as FilterInterface;
use ILIAS\UI\Component\Table\Data\Filter\Sort\FilterSortField as FilterSortFieldInterface;
use ILIAS\UI\Component\Table\Data\Filter\Storage\FilterStorage;
use ILIAS\UI\Component\Table\Data\Table;
use ILIAS\UI\Implementation\Component\Table\Data\Filter\Filter;
use ILIAS\UI\Implementation\Component\Table\Data\Filter\Sort\FilterSortField;

/**
 * Class AbstractFilterStorage
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data\Filter\Storage
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
abstract class AbstractFilterStorage implements FilterStorage {

	/**
	 * @inheritDoc
	 */
	public function __construct() {

	}


	/**
	 * @inheritDoc
	 */
	public function handleDefaultFilter(FilterInterface $filter, Table $component): FilterInterface {
		if (!$filter->isFilterSet() && empty($filter->getSortFields())) {
			$filter = $filter->withSortFields(array_map(function (Column $column) use ($component): FilterSortFieldInterface {
				return $this->sortField($column->getKey(), $column->getDefaultSortDirection());
			}, array_filter($component->getColumns(), function (Column $column): bool {
				return ($column->isSortable() && $column->isDefaultSort());
			})));
		}

		if (!$filter->isFilterSet() && empty($filter->getSelectedColumns())) {
			$filter = $filter->withSelectedColumns(array_map(function (Column $column): string {
				return $column->getKey();
			}, array_filter($component->getColumns(), function (Column $column): bool {
				return ($column->isSelectable() && $column->isDefaultSelected());
			})));
		}

		return $filter;
	}


	/**
	 * @inheritDoc
	 */
	public function filter(): FilterInterface {
		return new Filter();
	}


	/**
	 * @inheritDoc
	 */
	public function sortField(string $sort_field, int $sort_field_direction): FilterSortFieldInterface {
		return new FilterSortField($sort_field, $sort_field_direction);
	}


	/**
	 * @param string $string
	 *
	 * @return string
	 */
	protected function strToCamelCase(string $string): string {
		return str_replace("_", "", ucwords($string, "_"));
	}
}
