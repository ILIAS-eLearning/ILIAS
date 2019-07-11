<?php

namespace ILIAS\UI\Component\Table\Data\Column;

use ILIAS\UI\Component\Table\Data\Column\Formater\ColumnFormater;
use ILIAS\UI\Component\Table\Data\Export\Formater\ExportFormater;
use ILIAS\UI\Component\Table\Data\Filter\Sort\FilterSortField;

/**
 * Interface Column
 *
 * @package ILIAS\UI\Component\Table\Data\Column
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface Column {

	/**
	 * Column constructor
	 *
	 * @param string              $key
	 * @param string              $title
	 * @param ColumnFormater      $column_formater
	 * @param ExportFormater|null $export_formater
	 */
	public function __construct(string $key, string $title, ColumnFormater $column_formater, ?ExportFormater $export_formater = null);


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
	 * @return ColumnFormater
	 */
	public function getColumnFormater(): ColumnFormater;


	/**
	 * @param ColumnFormater $column_formater
	 *
	 * @return self
	 */
	public function withColumnFormater(ColumnFormater $column_formater): self;


	/**
	 * @return ExportFormater|null
	 */
	public function getExportFormater(): ?ExportFormater;


	/**
	 * @param ExportFormater|null $export_formater
	 *
	 * @return self
	 */
	public function withExportFormater(?ExportFormater $export_formater = null): self;


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
	public function withDefaultSortDirection(int $default_sort_direction = FilterSortField::SORT_DIRECTION_UP): self;


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
