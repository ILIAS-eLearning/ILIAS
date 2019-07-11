<?php

namespace ILIAS\UI\Implementation\Component\Table\Data;

use ILIAS\UI\Component\Input\Field\FilterInput;
use ILIAS\UI\Component\Table\Data\Column\Column;
use ILIAS\UI\Component\Table\Data\Data\Fetcher\DataFetcher;
use ILIAS\UI\Component\Table\Data\Table as TableInterface;
use ILIAS\UI\Component\Table\Data\Export\ExportFormat;
use ILIAS\UI\Component\Table\Data\Factory\Factory;
use ILIAS\UI\Component\Table\Data\Filter\Storage\FilterStorage;
use ILIAS\UI\Component\Table\Data\Filter\Filter;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class Table
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class Table implements TableInterface {

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
	protected $filter_position = Filter::FILTER_POSITION_TOP;
	/**
	 * @var Column[]
	 */
	protected $columns = [];
	/**
	 * @var DataFetcher
	 */
	protected $data_fetcher;
	/**
	 * @var FilterInput[]
	 */
	protected $filter_fields = [];
	/**
	 * @var ExportFormat[]
	 */
	protected $export_formats = [];
	/**
	 * @var string[]
	 */
	protected $multiple_actions = [];
	/**
	 * @var FilterStorage
	 */
	protected $filter_storage;
	/**
	 * @var Factory
	 */
	protected $factory;


	/**
	 * @inheritDoc
	 */
	public function __construct(string $table_id, string $action_url, string $title, array $columns, DataFetcher $data_fetcher, FilterStorage $filter_storage, Factory $factory) {
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
	public function withTableId(string $table_id): TableInterface {
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
	public function withActionUrl(string $action_url): TableInterface {
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
	public function withTitle(string $title): TableInterface {
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
	public function withFetchDataNeedsFilterFirstSet(bool $fetch_data_needs_filter_first_set = false): TableInterface {
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
	public function withFilterPosition(int $filter_position = Filter::FILTER_POSITION_TOP): TableInterface {
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
	public function withColumns(array $columns): TableInterface {
		$clone = clone $this;

		$clone->columns = $columns;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function getDataFetcher(): DataFetcher {
		return $this->data_fetcher;
	}


	/**
	 * @inheritDoc
	 */
	public function withFetchData(DataFetcher $data_fetcher): TableInterface {
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
	public function withFilterFields(array $filter_fields): TableInterface {
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
	public function withExportFormats(array $export_formats): TableInterface {
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
	public function withMultipleActions(array $multiple_actions): TableInterface {
		$clone = clone $this;

		$clone->multiple_actions = $multiple_actions;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function getFilterStorage(): FilterStorage {
		return $this->filter_storage;
	}


	/**
	 * @inheritDoc
	 */
	public function withFilterStorage(FilterStorage $filter_storage): TableInterface {
		$clone = clone $this;

		$clone->filter_storage = $filter_storage;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function getActionRowId(): string {
		return strval(filter_input(INPUT_GET, Renderer::actionParameter(TableInterface::ACTION_GET_VAR, $this->getTableId())));
	}


	/**
	 * @inheritDoc
	 */
	public function getMultipleActionRowIds(): array {
		return (filter_input(INPUT_POST, Renderer::actionParameter(TableInterface::MULTIPLE_SELECT_POST_VAR, $this->getTableId()), FILTER_DEFAULT, FILTER_FORCE_ARRAY)
			?? []);
	}


	/**
	 * @inheritDoc
	 */
	public function getFactory(): Factory {
		return $this->factory;
	}
}
