<?php

namespace ILIAS\UI\Implementation\Component\Table\Data\Factory;

use ILIAS\DI\Container;
use ILIAS\UI\Component\Table\Data\Column\Formater\ColumnFormater;
use ILIAS\UI\Component\Table\Data\Column\Column as ColumnInterface;
use ILIAS\UI\Component\Table\Data\Data\Fetcher\DataFetcher;
use ILIAS\UI\Component\Table\Data\Data\Row\RowData as RowDataInterface;
use ILIAS\UI\Component\Table\Data\Data\Data as DataInterface;
use ILIAS\UI\Component\Table\Data\Table as TableInterface;
use ILIAS\UI\Component\Table\Data\Export\Formater\ExportFormater;
use ILIAS\UI\Component\Table\Data\Export\ExportFormat;
use ILIAS\UI\Component\Table\Data\Factory\Factory as FactoryInterface;
use ILIAS\UI\Component\Table\Data\Filter\Sort\FilterSortField as FilterSortFieldInterface;
use ILIAS\UI\Component\Table\Data\Filter\Storage\FilterStorage;
use ILIAS\UI\Component\Table\Data\Filter\Filter as FilterInterface;
use ILIAS\UI\Implementation\Component\Table\Data\Column\Action\ActionColumn;
use ILIAS\UI\Implementation\Component\Table\Data\Column\Action\ActionColumnFormater;
use ILIAS\UI\Implementation\Component\Table\Data\Column\Column;
use ILIAS\UI\Implementation\Component\Table\Data\Data\Row\RowData;
use ILIAS\UI\Implementation\Component\Table\Data\Data\Data;
use ILIAS\UI\Implementation\Component\Table\Data\Table;
use ILIAS\UI\Implementation\Component\Table\Data\Export\CSVExportFormat;
use ILIAS\UI\Implementation\Component\Table\Data\Export\ExcelExportFormat;
use ILIAS\UI\Implementation\Component\Table\Data\Export\PDFExportFormat;
use ILIAS\UI\Implementation\Component\Table\Data\Filter\Sort\FilterSortField;
use ILIAS\UI\Implementation\Component\Table\Data\Filter\Filter;

/**
 * Class Factory
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data\Factory
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class Factory implements FactoryInterface {

	/**
	 * @var Container
	 */
	protected $dic;


	/**
	 * @inheritDoc
	 */
	public function __construct(Container $dic) {
		$this->dic = $dic;
	}


	/**
	 * @inheritDoc
	 */
	public function table(string $id, string $action_url, string $title, array $columns, DataFetcher $data_fetcher, FilterStorage $filter_storage): TableInterface {
		return new Table($id, $action_url, $title, $columns, $data_fetcher, $filter_storage, $this);
	}


	/**
	 * @inheritDoc
	 */
	public function column(string $key, string $title, ColumnFormater $column_formater, ExportFormater $export_formater): ColumnInterface {
		return new Column($key, $title, $column_formater, $export_formater);
	}


	/**
	 * @inheritDoc
	 */
	public function actionColumn(string $key, string $title, array $actions): ColumnInterface {
		return (new ActionColumn($key, $title, new ActionColumnFormater($this->dic)))->withActions($actions)->withSortable(false)
			->withSelectable(false);
	}


	/**
	 * @inheritDoc
	 */
	public function data(array $data, int $max_count): DataInterface {
		return new Data($data, $max_count);
	}


	/**
	 * @inheritDoc
	 */
	public function filter(string $table_id, int $user_id): FilterInterface {
		return new Filter($table_id, $user_id);
	}


	/**
	 * @inheritDoc
	 */
	public function filterSortField(string $sort_field, int $sort_field_direction): FilterSortFieldInterface {
		return new FilterSortField($sort_field, $sort_field_direction);
	}


	/**
	 * @inheritDoc
	 */
	public function rowData(string $row_id, object $original_data): RowDataInterface {
		return new RowData($row_id, $original_data);
	}


	/**
	 * @inheritDoc
	 */
	public function exportFormatCSV(): ExportFormat {
		return new CSVExportFormat($this->dic);
	}


	/**
	 * @inheritDoc
	 */
	public function exportFormatExcel(): ExportFormat {
		return new ExcelExportFormat($this->dic);
	}


	/**
	 * @inheritDoc
	 */
	public function exportFormatPDF(): ExportFormat {
		return new PDFExportFormat($this->dic);
	}
}
