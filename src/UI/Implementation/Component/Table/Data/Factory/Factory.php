<?php

namespace ILIAS\UI\Implementation\Component\Table\Data\Factory;

use ILIAS\DI\Container;
use ILIAS\UI\Component\Table\Data\Column\Formater\TableColumnFormater;
use ILIAS\UI\Component\Table\Data\Column\TableColumn as TableColumnInterface;
use ILIAS\UI\Component\Table\Data\Data\Fetcher\TableDataFetcher;
use ILIAS\UI\Component\Table\Data\Data\Row\TableRowData as TableRowDataInterface;
use ILIAS\UI\Component\Table\Data\Data\TableData as TableDataInterface;
use ILIAS\UI\Component\Table\Data\DataTable as DataTableInterface;
use ILIAS\UI\Component\Table\Data\Export\Formater\TableExportFormater;
use ILIAS\UI\Component\Table\Data\Export\TableExportFormat;
use ILIAS\UI\Component\Table\Data\Factory\Factory as FactoryInterface;
use ILIAS\UI\Component\Table\Data\Filter\Sort\TableFilterSortField as TableFilterSortFieldInterface;
use ILIAS\UI\Component\Table\Data\Filter\Storage\TableFilterStorage;
use ILIAS\UI\Component\Table\Data\Filter\TableFilter as TableFilterInterface;
use ILIAS\UI\Implementation\Component\Table\Data\Column\Action\ActionTableColumn;
use ILIAS\UI\Implementation\Component\Table\Data\Column\Action\ActionTableColumnFormater;
use ILIAS\UI\Implementation\Component\Table\Data\Column\TableColumn;
use ILIAS\UI\Implementation\Component\Table\Data\Data\Row\TableRowData;
use ILIAS\UI\Implementation\Component\Table\Data\Data\TableData;
use ILIAS\UI\Implementation\Component\Table\Data\DataTable;
use ILIAS\UI\Implementation\Component\Table\Data\Export\TableCSVTableExportFormat;
use ILIAS\UI\Implementation\Component\Table\Data\Export\TableExcelTableExportFormat;
use ILIAS\UI\Implementation\Component\Table\Data\Export\TablePDFTableExportFormat;
use ILIAS\UI\Implementation\Component\Table\Data\Filter\Sort\TableFilterSortField;
use ILIAS\UI\Implementation\Component\Table\Data\Filter\TableFilter;

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
	public function table(string $id, string $action_url, string $title, array $columns, TableDataFetcher $data_fetcher, TableFilterStorage $filter_storage): DataTableInterface {
		return new DataTable($id, $action_url, $title, $columns, $data_fetcher, $filter_storage, $this);
	}


	/**
	 * @inheritDoc
	 */
	public function column(string $key, string $title, TableColumnFormater $column_formater, TableExportFormater $export_formater): TableColumnInterface {
		return new TableColumn($key, $title, $column_formater, $export_formater);
	}


	/**
	 * @inheritDoc
	 */
	public function actionColumn(string $key, string $title, array $actions): TableColumnInterface {
		return (new ActionTableColumn($key, $title, new ActionTableColumnFormater($this->dic)))->withActions($actions)->withSortable(false)
			->withSelectable(false);
	}


	/**
	 * @inheritDoc
	 */
	public function data(array $data, int $max_count): TableDataInterface {
		return new TableData($data, $max_count);
	}


	/**
	 * @inheritDoc
	 */
	public function filter(string $table_id, int $user_id): TableFilterInterface {
		return new TableFilter($table_id, $user_id);
	}


	/**
	 * @inheritDoc
	 */
	public function filterSortField(string $sort_field, int $sort_field_direction): TableFilterSortFieldInterface {
		return new TableFilterSortField($sort_field, $sort_field_direction);
	}


	/**
	 * @inheritDoc
	 */
	public function rowData(string $row_id, object $original_data): TableRowDataInterface {
		return new TableRowData($row_id, $original_data);
	}


	/**
	 * @inheritDoc
	 */
	public function exportFormatCSV(): TableExportFormat {
		return new TableCSVTableExportFormat($this->dic);
	}


	/**
	 * @inheritDoc
	 */
	public function exportFormatExcel(): TableExportFormat {
		return new TableExcelTableExportFormat($this->dic);
	}


	/**
	 * @inheritDoc
	 */
	public function exportFormatPDF(): TableExportFormat {
		return new TablePDFTableExportFormat($this->dic);
	}
}
