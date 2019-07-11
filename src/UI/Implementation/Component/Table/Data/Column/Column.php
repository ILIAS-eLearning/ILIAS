<?php

namespace ILIAS\UI\Implementation\Component\Table\Data\Column;

use ILIAS\UI\Component\Table\Data\Column\Formater\ColumnFormater;
use ILIAS\UI\Component\Table\Data\Column\Column as ColumnInterface;
use ILIAS\UI\Component\Table\Data\Export\Formater\ExportFormater;
use ILIAS\UI\Component\Table\Data\Filter\Sort\FilterSortField as FilterSortFieldInterface;

/**
 * Class Column
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data\Column
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class Column implements ColumnInterface {

	/**
	 * @var string
	 */
	protected $key = "";
	/**
	 * @var string
	 */
	protected $title = "";
	/**
	 * @var ColumnFormater
	 */
	protected $column_formater;
	/**
	 * @var ExportFormater|null
	 */
	protected $export_formater = null;
	/**
	 * @var bool
	 */
	protected $sortable = true;
	/**
	 * @var bool
	 */
	protected $default_sort = false;
	/**
	 * @var int
	 */
	protected $default_sort_direction = FilterSortFieldInterface::SORT_DIRECTION_UP;
	/**
	 * @var bool
	 */
	protected $selectable = true;
	/**
	 * @var bool
	 */
	protected $default_selected = true;
	/**
	 * @var bool
	 */
	protected $dragable = false;


	/**
	 * @inheritDoc
	 */
	public function __construct(string $key, string $title, ColumnFormater $column_formater, ?ExportFormater $export_formater = null) {
		$this->key = $key;

		$this->title = $title;

		$this->column_formater = $column_formater;

		$this->export_formater = $export_formater;
	}


	/**
	 * @inheritDoc
	 */
	public function getKey(): string {
		return $this->key;
	}


	/**
	 * @inheritDoc
	 */
	public function withKey(string $key): ColumnInterface {
		$clone = clone $this;

		$clone->key = $key;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function getTitle(): string {
		return $this->title;
	}


	/**
	 * @inheritDoc
	 */
	public function withTitle(string $title): ColumnInterface {
		$clone = clone $this;

		$clone->title = $title;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function getColumnFormater(): ColumnFormater {
		return $this->column_formater;
	}


	/**
	 * @inheritDoc
	 */
	public function withColumnFormater(ColumnFormater $column_formater): ColumnInterface {
		$clone = clone $this;

		$clone->column_formater = $column_formater;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function getExportFormater(): ?ExportFormater {
		return $this->export_formater;
	}


	/**
	 * @inheritDoc
	 */
	public function withExportFormater(?ExportFormater $export_formater = null): ColumnInterface {
		$clone = clone $this;

		$clone->export_formater = $export_formater;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function isSortable(): bool {
		return $this->sortable;
	}


	/**
	 * @inheritDoc
	 */
	public function withSortable(bool $sortable = true): ColumnInterface {
		$clone = clone $this;

		$clone->sortable = $sortable;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function isDefaultSort(): bool {
		return $this->default_sort;
	}


	/**
	 * @inheritDoc
	 */
	public function withDefaultSort(bool $default_sort = false): ColumnInterface {
		$clone = clone $this;

		$clone->default_sort = $default_sort;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function getDefaultSortDirection(): int {
		return $this->default_sort_direction;
	}


	/**
	 * @inheritDoc
	 */
	public function withDefaultSortDirection(int $default_sort_direction = FilterSortFieldInterface::SORT_DIRECTION_UP): ColumnInterface {
		$clone = clone $this;

		$clone->default_sort_direction = $default_sort_direction;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function isSelectable(): bool {
		return $this->selectable;
	}


	/**
	 * @inheritDoc
	 */
	public function withSelectable(bool $selectable = true): ColumnInterface {
		$clone = clone $this;

		$clone->selectable = $selectable;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function isDefaultSelected(): bool {
		return $this->default_selected;
	}


	/**
	 * @inheritDoc
	 */
	public function withDefaultSelected(bool $default_selected = true): ColumnInterface {
		$clone = clone $this;

		$clone->default_selected = $default_selected;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function isDragable(): bool {
		return $this->dragable;
	}


	/**
	 * @inheritDoc
	 */
	public function withDragable(bool $dragable = false): ColumnInterface {
		$clone = clone $this;

		$clone->dragable = $dragable;

		return $clone;
	}
}
