<?php

namespace ILIAS\UI\Component\Table\Data\Filter\Storage;

use ILIAS\UI\Component\Table\Data\Filter\Filter;
use ILIAS\UI\Component\Table\Data\Filter\Sort\FilterSortField;
use ILIAS\UI\Component\Table\Data\Table;

/**
 * Interface FilterStorage
 *
 * @package ILIAS\UI\Component\Table\Data\Filter\Storage
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface FilterStorage {

	/**
	 * @var string
	 */
	const VAR_SORT_FIELDS = "sort_fields";
	/**
	 * @var string
	 */
	const VAR_SORT_FIELD = "sort_field";
	/**
	 * @var string
	 */
	const VAR_REMOVE_SORT_FIELD = "remove_sort_field";
	/**
	 * @var string
	 */
	const VAR_SORT_FIELD_DIRECTION = "sort_field_direction";
	/**
	 * @var string
	 */
	const VAR_ROWS_COUNT = "rows_count";
	/**
	 * @var string
	 */
	const VAR_CURRENT_PAGE = "current_page";
	/**
	 * @var string
	 */
	const VAR_FIELD_VALUES = "field_values";
	/**
	 * @var string
	 */
	const VAR_SELECTED_COLUMNS = "selected_columns";
	/**
	 * @var string
	 */
	const VAR_SELECT_COLUMN = "select_column";
	/**
	 * @var string
	 */
	const VAR_DESELECT_COLUMN = "deselect_column";
	/**
	 * @var string
	 */
	const VAR_EXPORT_FORMAT_ID = "export_format_id";
	/**
	 * @var string[]
	 */
	const VARS = [
		self::VAR_SORT_FIELDS,
		self::VAR_ROWS_COUNT,
		self::VAR_CURRENT_PAGE,
		self::VAR_FIELD_VALUES,
		self::VAR_SELECTED_COLUMNS
	];


	/**
	 * FilterStorage constructor
	 */
	public function __construct();


	/**
	 * @param string $table_id
	 * @param int    $user_id
	 *
	 * @return Filter
	 */
	public function read(string $table_id, int $user_id): Filter;


	/**
	 * @param Filter $filter
	 * @param Table  $component
	 *
	 * @return Filter
	 */
	public function handleDefaultFilter(Filter $filter, Table $component): Filter;


	/**
	 * @param Filter $filter
	 * @param string $table_id
	 * @param int    $user_id
	 */
	public function store(Filter $filter, string $table_id, int $user_id): void;


	/**
	 * @return Filter
	 */
	public function filter(): Filter;


	/**
	 * @param string $sort_field
	 * @param int    $sort_field_direction
	 *
	 * @return FilterSortField
	 */
	public function sortField(string $sort_field, int $sort_field_direction): FilterSortField;
}
