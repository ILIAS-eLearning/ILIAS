<?php

namespace ILIAS\UI\Implementation\Component\Table\Data\Column;

use ILIAS\UI\Component\Table\Data\Column\Formater\TableColumnFormater;
use ILIAS\UI\Component\Table\Data\Column\TableColumn as TableColumnInterface;
use ILIAS\UI\Component\Table\Data\Export\Formater\TableExportFormater;
use ILIAS\UI\Component\Table\Data\Filter\Sort\TableFilterSortField as TableFilterSortFieldInterface;

/**
 * Class TableColumn
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data\Column
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class TableColumn implements TableColumnInterface {

	/**
	 * @var string
	 */
	protected $key = "";
	/**
	 * @var string
	 */
	protected $title = "";
	/**
	 * @var TableColumnFormater
	 */
	protected $column_formater;
	/**
	 * @var TableExportFormater|null
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
	protected $default_sort_direction = TableFilterSortFieldInterface::SORT_DIRECTION_UP;
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
	public function __construct(string $key, string $title, TableColumnFormater $column_formater, ?TableExportFormater $export_formater = null) {
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
	public function withKey(string $key): TableColumnInterface {
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
	public function withTitle(string $title): TableColumnInterface {
		$clone = clone $this;

		$clone->title = $title;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function getColumnFormater(): TableColumnFormater {
		return $this->column_formater;
	}


	/**
	 * @inheritDoc
	 */
	public function withColumnFormater(TableColumnFormater $column_formater): TableColumnInterface {
		$clone = clone $this;

		$clone->column_formater = $column_formater;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function getExportFormater(): ?TableExportFormater {
		return $this->export_formater;
	}


	/**
	 * @inheritDoc
	 */
	public function withExportFormater(?TableExportFormater $export_formater = null): TableColumnInterface {
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
	public function withSortable(bool $sortable = true): TableColumnInterface {
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
	public function withDefaultSort(bool $default_sort = false): TableColumnInterface {
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
	public function withDefaultSortDirection(int $default_sort_direction = TableFilterSortFieldInterface::SORT_DIRECTION_UP): TableColumnInterface {
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
	public function withSelectable(bool $selectable = true): TableColumnInterface {
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
	public function withDefaultSelected(bool $default_selected = true): TableColumnInterface {
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
	public function withDragable(bool $dragable = false): TableColumnInterface {
		$clone = clone $this;

		$clone->dragable = $dragable;

		return $clone;
	}
}
