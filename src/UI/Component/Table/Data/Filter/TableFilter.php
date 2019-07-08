<?php

namespace ILIAS\UI\Component\Table\Data\Filter;

use ILIAS\UI\Component\Table\Data\Filter\Sort\TableFilterSortField;

/**
 * Interface TableFilter
 *
 * @package ILIAS\UI\Component\Table\Data\Filter
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface TableFilter {

	/**
	 * @var int
	 */
	const FILTER_POSITION_TOP = 1;
	/**
	 * @var int
	 */
	const FILTER_POSITION_BOTTOM = 2;
	/**
	 * @var int
	 */
	const DEFAULT_ROWS_COUNT = 50;
	/**
	 * @var int[]
	 */
	const ROWS_COUNT = [
		5,
		10,
		15,
		20,
		30,
		40,
		self::DEFAULT_ROWS_COUNT,
		100,
		200,
		400,
		800
	];


	/**
	 * TableFilter constructor
	 *
	 * @param string $table_id
	 * @param int    $user_id
	 */
	public function __construct(string $table_id, int $user_id);


	/**
	 * @return string
	 */
	public function getTableId(): string;


	/**
	 * @param string $table_id
	 *
	 * @return self
	 */
	public function withTableId(string $table_id): self;


	/**
	 * @return int
	 */
	public function getUserId(): int;


	/**
	 * @param int $user_id
	 *
	 * @return self
	 */
	public function withUserId(int $user_id): self;


	/**
	 * @param mixed[] $key
	 *
	 * @return array
	 */
	public function getFieldValues(): array;


	/**
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function getFieldValue(string $key);


	/**
	 * @param mixed[] $field_values
	 *
	 * @return self
	 */
	public function withFieldValues(array $field_values): self;


	/**
	 * @return TableFilterSortField[]
	 */
	public function getSortFields(): array;


	/**
	 * @param string $sort_field
	 *
	 * @return TableFilterSortField|null
	 */
	public function getSortField(string $sort_field): ?TableFilterSortField;


	/**
	 * @param TableFilterSortField[] $sort_fields
	 *
	 * @return self
	 */
	public function withSortFields(array $sort_fields): self;


	/**
	 * @param TableFilterSortField $sort_field
	 *
	 * @return self
	 */
	public function addSortField(TableFilterSortField $sort_field): self;


	/**
	 * @param string $sort_field
	 *
	 * @return self
	 */
	public function removeSortField(string $sort_field): self;


	/**
	 * @return string[]
	 */
	public function getSelectedColumns(): array;


	/**
	 * @param string[] $selected_columns
	 *
	 * @return self
	 */
	public function withSelectedColumns(array $selected_columns): self;


	/**
	 * @param string $selected_column
	 *
	 * @return self
	 */
	public function selectColumn(string $selected_column): self;


	/**
	 * @param string $selected_column
	 *
	 * @return self
	 */
	public function deselectColumn(string $selected_column): self;


	/**
	 * @return bool
	 */
	public function isFilterSet(): bool;


	/**
	 * @param bool $filter_set
	 *
	 * @return self
	 */
	public function withFilterSet(bool $filter_set = false): self;


	/**
	 * @return int
	 */
	public function getRowsCount(): int;


	/**
	 * @param int $rows_count
	 *
	 * @return self
	 */
	public function withRowsCount(int $rows_count = self::DEFAULT_ROWS_COUNT): self;


	/**
	 * @return int
	 */
	public function getCurrentPage(): int;


	/**
	 * @param int $current_page
	 *
	 * @return self
	 */
	public function withCurrentPage(int $current_page = 1): self;


	/**
	 * @param int $max_count
	 *
	 * @return int
	 */
	public function getTotalPages(int $max_count): int;


	/**
	 * @return int
	 */
	public function getLimitStart(): int;


	/**
	 * @return int
	 */
	public function getLimitEnd(): int;
}
