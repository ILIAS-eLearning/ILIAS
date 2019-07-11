<?php

namespace ILIAS\UI\Component\Table\Data;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Input\Field\FilterInput;
use ILIAS\UI\Component\Table\Data\Column\Column;
use ILIAS\UI\Component\Table\Data\Data\Fetcher\DataFetcher;
use ILIAS\UI\Component\Table\Data\Export\ExportFormat;
use ILIAS\UI\Component\Table\Data\Factory\Factory;
use ILIAS\UI\Component\Table\Data\Filter\Storage\FilterStorage;
use ILIAS\UI\Component\Table\Data\Filter\Filter;

/**
 * Interface Table
 *
 * @package ILIAS\UI\Component\Table\Data
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface Table extends Component {

	const ACTION_GET_VAR = "row_id";
	const MULTIPLE_SELECT_POST_VAR = "selected_row_ids";
	const LANG_MODULE = "datatable";


	/**
	 * Table constructor
	 *
	 * @param string        $table_id
	 * @param string        $action_url
	 * @param string        $title
	 * @param Column[]      $columns
	 * @param DataFetcher   $data_fetcher
	 * @param FilterStorage $filter_storage
	 * @param Factory       $factory
	 */
	public function __construct(string $table_id, string $action_url, string $title, array $columns, DataFetcher $data_fetcher, FilterStorage $filter_storage, Factory $factory);


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
	 * @return string
	 */
	public function getActionUrl(): string;


	/**
	 * @param string $action_url
	 *
	 * @return self
	 */
	public function withActionUrl(string $action_url): self;


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
	 * @return bool
	 */
	public function isFetchDataNeedsFilterFirstSet(): bool;


	/**
	 * @param bool $fetch_data_needs_filter_first_set
	 *
	 * @return self
	 */
	public function withFetchDataNeedsFilterFirstSet(bool $fetch_data_needs_filter_first_set = false): self;


	/**
	 * @return int
	 */
	public function getFilterPosition(): int;


	/**
	 * @param int $filter_position
	 *
	 * @return self
	 */
	public function withFilterPosition(int $filter_position = Filter::FILTER_POSITION_TOP): self;


	/**
	 * @return Column[]
	 */
	public function getColumns(): array;


	/**
	 * @param Column[] $columns
	 *
	 * @return self
	 */
	public function withColumns(array $columns): self;


	/**
	 * @return DataFetcher
	 */
	public function getDataFetcher(): DataFetcher;


	/**
	 * @param DataFetcher $data_fetcher
	 *
	 * @return self
	 */
	public function withFetchData(DataFetcher $data_fetcher): self;


	/**
	 * @return FilterInput[]
	 */
	public function getFilterFields(): array;


	/**
	 * @param FilterInput[] $filter_fields
	 *
	 * @return self
	 */
	public function withFilterFields(array $filter_fields): self;


	/**
	 * @return ExportFormat[]
	 */
	public function getExportFormats(): array;


	/**
	 * @param ExportFormat[] $export_formats
	 *
	 * @return self
	 */
	public function withExportFormats(array $export_formats): self;


	/**
	 * @return string[]
	 */
	public function getMultipleActions(): array;


	/**
	 * @param string[] $multiple_actions
	 *
	 * @return self
	 */
	public function withMultipleActions(array $multiple_actions): self;


	/**
	 * @return FilterStorage
	 */
	public function getFilterStorage(): FilterStorage;


	/**
	 * @param FilterStorage $filter_storage
	 *
	 * @return self
	 */
	public function withFilterStorage(FilterStorage $filter_storage): self;


	/**
	 * @return string
	 */
	public function getActionRowId(): string;


	/**
	 * @return string[]
	 */
	public function getMultipleActionRowIds(): array;


	/**
	 * @return Factory
	 */
	public function getFactory(): Factory;
}
