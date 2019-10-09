<?php

declare(strict_types=1);

namespace ILIAS\UI\Component\Table\Data\Settings;

use ILIAS\UI\Component\Table\Data\Data\Data;
use ILIAS\UI\Component\Table\Data\Settings\Sort\SortField;
use ILIAS\UI\Component\ViewControl\Pagination;

/**
 * Interface Settings
 *
 * @package ILIAS\UI\Component\Table\Data\Settings
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface Settings
{

    /**
     * @var int
     */
    const DEFAULT_ROWS_COUNT = 50;
    /**
     * @var int[]
     */
    const ROWS_COUNT
        = [
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
     * @param mixed[] $key
     *
     * @return array
     */
    public function getFilterFieldValues() : array;


    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getFilterFieldValue(string $key);


    /**
     * @param mixed[] $filter_field_values
     *
     * @return self
     */
    public function withFilterFieldValues(array $filter_field_values) : self;


    /**
     * @return SortField[]
     */
    public function getSortFields() : array;


    /**
     * @param string $sort_field
     *
     * @return SortField|null
     */
    public function getSortField(string $sort_field) : ?SortField;


    /**
     * @param SortField[] $sort_fields
     *
     * @return self
     */
    public function withSortFields(array $sort_fields) : self;


    /**
     * @param SortField $sort_field
     *
     * @return self
     */
    public function addSortField(SortField $sort_field) : self;


    /**
     * @param string $sort_field
     *
     * @return self
     */
    public function removeSortField(string $sort_field) : self;


    /**
     * @return string[]
     */
    public function getSelectedColumns() : array;


    /**
     * @param string[] $selected_columns
     *
     * @return self
     */
    public function withSelectedColumns(array $selected_columns) : self;


    /**
     * @param string $selected_column
     *
     * @return self
     */
    public function selectColumn(string $selected_column) : self;


    /**
     * @param string $selected_column
     *
     * @return self
     */
    public function deselectColumn(string $selected_column) : self;


    /**
     * @return bool
     */
    public function isFilterSet() : bool;


    /**
     * @param bool $filter_set
     *
     * @return self
     */
    public function withFilterSet(bool $filter_set = false) : self;


    /**
     * @return int
     */
    public function getRowsCount() : int;


    /**
     * @param int $rows_count
     *
     * @return self
     */
    public function withRowsCount(int $rows_count = self::DEFAULT_ROWS_COUNT) : self;


    /**
     * @return int
     */
    public function getCurrentPage() : int;


    /**
     * @param int $current_page
     *
     * @return self
     */
    public function withCurrentPage(int $current_page = 0) : self;


    /**
     * @return int
     */
    public function getOffset() : int;


    /**
     * @param Data|null $data
     *
     * @return Pagination
     *
     * @internal
     */
    public function getPagination(?Data $data) : Pagination;
}
