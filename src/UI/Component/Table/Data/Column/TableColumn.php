<?php

namespace ILIAS\UI\Component\Table\Data\Column;

use ILIAS\UI\Component\Table\Data\Column\Formater\TableColumnFormater;
use ILIAS\UI\Component\Table\Data\Export\Formater\TableExportFormater;
use ILIAS\UI\Component\Table\Data\Filter\Sort\TableFilterSortField;

/**
 * Interface TableColumn
 *
 * @package ILIAS\UI\Component\Table\Data\Column
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface TableColumn {

	/**
	 * TableColumn constructor
	 *
	 * @param string                   $key
	 * @param string                   $title
	 * @param TableColumnFormater      $column_formater
	 * @param TableExportFormater|null $export_formater
	 */
	public function __construct(string $key, string $title, TableColumnFormater $column_formater, ?TableExportFormater $export_formater = null);


	/**
	 * @return string
	 */
	public function getKey(): string;


	/**
	 * @param string $key
	 *
	 * @return self
	 */
	public function withKey(string $key): self;


	/**
	 * @return string
	 */
	public function getTitle(): string;


	/**
	 * @param string $title
	 *
	 * @return self
	 */
	public function withTitle(string $title): self;


	/**
	 * @return TableColumnFormater
	 */
	public function getColumnFormater(): TableColumnFormater;


	/**
	 * @param TableColumnFormater $column_formater
	 *
	 * @return self
	 */
	public function withColumnFormater(TableColumnFormater $column_formater): self;


	/**
	 * @return TableExportFormater|null
	 */
	public function getExportFormater(): ?TableExportFormater;


	/**
	 * @param TableExportFormater|null $export_formater
	 *
	 * @return self
	 */
	public function withExportFormater(?TableExportFormater $export_formater = null): self;


	/**
	 * @return bool
	 */
	public function isSortable(): bool;


	/**
	 * @param bool $sortable
	 *
	 * @return self
	 */
	public function withSortable(bool $sortable = true): self;


	/**
	 * @return bool
	 */
	public function isDefaultSort(): bool;


	/**
	 * @param bool $default_sort
	 *
	 * @return self
	 */
	public function withDefaultSort(bool $default_sort = false): self;


	/**
	 * @return int
	 */
	public function getDefaultSortDirection(): int;


	/**
	 * @param int $default_sort_direction
	 *
	 * @return self
	 */
	public function withDefaultSortDirection(int $default_sort_direction = TableFilterSortField::SORT_DIRECTION_UP): self;


	/**
	 * @return bool
	 */
	public function isSelectable(): bool;


	/**
	 * @param bool $selectable
	 *
	 * @return self
	 */
	public function withSelectable(bool $selectable = true): self;


	/**
	 * @return bool
	 */
	public function isDefaultSelected(): bool;


	/**
	 * @param bool $default_selected
	 *
	 * @return self
	 */
	public function withDefaultSelected(bool $default_selected = true): self;


	/**
	 * @return bool
	 */
	public function isDragable(): bool;


	/**
	 * @param bool $dragable
	 *
	 * @return self
	 */
	public function withDragable(bool $dragable = false): self;
}
