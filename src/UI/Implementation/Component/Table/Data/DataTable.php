<?php

namespace ILIAS\UI\Implementation\Component\Table\Data;

use ILIAS\UI\Component\Input\Field\FilterInput;
use ILIAS\UI\Component\Table\Data\Column\TableColumn;
use ILIAS\UI\Component\Table\Data\Data\Fetcher\TableDataFetcher;
use ILIAS\UI\Component\Table\Data\DataTable as DataTableInterface;
use ILIAS\UI\Component\Table\Data\Export\TableExportFormat;
use ILIAS\UI\Component\Table\Data\Factory\Factory;
use ILIAS\UI\Component\Table\Data\Filter\Storage\TableFilterStorage;
use ILIAS\UI\Component\Table\Data\Filter\TableFilter;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class DataTable
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class DataTable implements DataTableInterface {

	use ComponentHelper;
	/**
	 * @var string
	 */
	protected $table_id = "";
	/**
	 * @var string
	 */
	protected $action_url = "";
	/**
	 * @var string
	 */
	protected $title = "";
	/**
	 * @var bool
	 */
	protected $fetch_data_needs_filter_first_set = false;
	/**
	 * @var int
	 */
	protected $filter_position = TableFilter::FILTER_POSITION_TOP;
	/**
	 * @var TableColumn[]
	 */
	protected $columns = [];
	/**
	 * @var TableDataFetcher
	 */
	protected $data_fetcher;
	/**
	 * @var FilterInput[]
	 */
	protected $filter_fields = [];
	/**
	 * @var TableExportFormat[]
	 */
	protected $export_formats = [];
	/**
	 * @var string[]
	 */
	protected $multiple_actions = [];
	/**
	 * @var TableFilterStorage
	 */
	protected $filter_storage;
	/**
	 * @var Factory
	 */
	protected $factory;


	/**
	 * @inheritDoc
	 */
	public function __construct(string $table_id, string $action_url, string $title, array $columns, TableDataFetcher $data_fetcher, TableFilterStorage $filter_storage, Factory $factory) {
		$this->table_id = $table_id;

		$this->action_url = $action_url;

		$this->title = $title;

		$this->columns = $columns;

		$this->data_fetcher = $data_fetcher;

		$this->filter_storage = $filter_storage;

		$this->factory = $factory;
	}


	/**
	 * @inheritDoc
	 */
	public function getTableId(): string {
		return $this->table_id;
	}


	/**
	 * @inheritDoc
	 */
	public function withTableId(string $table_id): DataTableInterface {
		$clone = clone $this;

		$clone->table_id = $table_id;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function getActionUrl(): string {
		return $this->action_url;
	}


	/**
	 * @inheritDoc
	 */
	public function withActionUrl(string $action_url): DataTableInterface {
		$clone = clone $this;

		$clone->action_url = $action_url;

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
	public function withTitle(string $title): DataTableInterface {
		$clone = clone $this;

		$clone->title = $title;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function isFetchDataNeedsFilterFirstSet(): bool {
		return $this->fetch_data_needs_filter_first_set;
	}


	/**
	 * @inheritDoc
	 */
	public function withFetchDataNeedsFilterFirstSet(bool $fetch_data_needs_filter_first_set = false): DataTableInterface {
		$clone = clone $this;

		$clone->fetch_data_needs_filter_first_set = $fetch_data_needs_filter_first_set;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function getFilterPosition(): int {
		return $this->filter_position;
	}


	/**
	 * @inheritDoc
	 */
	public function withFilterPosition(int $filter_position = TableFilter::FILTER_POSITION_TOP): DataTableInterface {
		$clone = clone $this;

		$clone->filter_position = $filter_position;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function getColumns(): array {
		return $this->columns;
	}


	/**
	 * @inheritDoc
	 */
	public function withColumns(array $columns): DataTableInterface {
		$clone = clone $this;

		$clone->columns = $columns;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function getDataFetcher(): TableDataFetcher {
		return $this->data_fetcher;
	}


	/**
	 * @inheritDoc
	 */
	public function withFetchData(TableDataFetcher $data_fetcher): DataTableInterface {
		$clone = clone $this;

		$clone->data_fetcher = $data_fetcher;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function getFilterFields(): array {
		return $this->filter_fields;
	}


	/**
	 * @inheritDoc
	 */
	public function withFilterFields(array $filter_fields): DataTableInterface {
		$clone = clone $this;

		$clone->filter_fields = $filter_fields;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function getExportFormats(): array {
		return $this->export_formats;
	}


	/**
	 * @inheritDoc
	 */
	public function withExportFormats(array $export_formats): DataTableInterface {
		$clone = clone $this;

		$clone->export_formats = $export_formats;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function getMultipleActions(): array {
		return $this->multiple_actions;
	}


	/**
	 * @inheritDoc
	 */
	public function withMultipleActions(array $multiple_actions): DataTableInterface {
		$clone = clone $this;

		$clone->multiple_actions = $multiple_actions;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function getFilterStorage(): TableFilterStorage {
		return $this->filter_storage;
	}


	/**
	 * @inheritDoc
	 */
	public function withFilterStorage(TableFilterStorage $filter_storage): DataTableInterface {
		$clone = clone $this;

		$clone->filter_storage = $filter_storage;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function getActionRowId(): string {
		return strval(filter_input(INPUT_GET, Renderer::actionParameter(DataTableInterface::ACTION_GET_VAR, $this->getTableId())));
	}


	/**
	 * @inheritDoc
	 */
	public function getMultipleActionRowIds(): array {
		return (filter_input(INPUT_POST, Renderer::actionParameter(DataTableInterface::MULTIPLE_SELECT_POST_VAR, $this->getTableId()), FILTER_DEFAULT, FILTER_FORCE_ARRAY)
			?? []);
	}


	/**
	 * @inheritDoc
	 */
	public function getFactory(): Factory {
		return $this->factory;
	}
}
