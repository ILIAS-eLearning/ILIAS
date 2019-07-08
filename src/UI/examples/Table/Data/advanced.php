<?php

use ILIAS\UI\Component\Table\Data\Column\TableColumn;
use ILIAS\UI\Component\Table\Data\Data\Row\TableRowData;
use ILIAS\UI\Component\Table\Data\Data\TableData as TableDataInterface;
use ILIAS\UI\Component\Table\Data\Factory\Factory;
use ILIAS\UI\Component\Table\Data\Filter\Sort\TableFilterSortField;
use ILIAS\UI\Component\Table\Data\Filter\TableFilter;
use ILIAS\UI\Implementation\Component\Table\Data\Column\Formater\SimplePropertyTableColumnFormater;
use ILIAS\UI\Implementation\Component\Table\Data\Column\Formater\SimplePropertyTableExportFormater;
use ILIAS\UI\Implementation\Component\Table\Data\Data\Fetcher\AbstractTableDataFetcher;
use ILIAS\UI\Implementation\Component\Table\Data\Data\TableData;
use ILIAS\UI\Implementation\Component\Table\Data\Export\Formater\AbstractTableColumnFormater;
use ILIAS\UI\Implementation\Component\Table\Data\Filter\Storage\TableFilterStorage;
use ILIAS\UI\Renderer;

/**
 * @return string
 */
function advanced(): string {
	global $DIC;

	$action_url = $DIC->ctrl()->getLinkTargetByClass(ilSystemStyleDocumentationGUI::class) . "&node_id=TableDataData";

	$factory = $DIC->ui()->factory()->table()->data($DIC);

	$table = $factory->table("example_datatable_advanced", $action_url, "Advanced example data table", [
		$factory->column("obj_id", "Id", new SimplePropertyTableColumnFormater($DIC), new SimplePropertyTableExportFormater($DIC))
			->withDefaultSelected(false),
		$factory->column("title", "Title", new SimplePropertyTableColumnFormater($DIC), new SimplePropertyTableExportFormater($DIC))
			->withDefaultSort(true),
		$factory->column("type", "Type", new class($DIC) extends AbstractTableColumnFormater {

			/**
			 * @inheritDoc
			 */
			public function formatHeader(TableColumn $column, string $table_id, Renderer $renderer): string {
				return $column->getTitle();
			}


			/**
			 * @inheritDoc
			 */
			public function formatRow(TableColumn $column, TableRowData $row, string $table_id, Renderer $renderer): string {
				$type = $row->getOriginalData()->{$column->getKey()};

				return $renderer->render([
					$this->dic->ui()->factory()->symbol()->icon()->custom(ilObject::_getIcon($row->getRowId(), "small"), $type),
					$this->dic->ui()->factory()->legacy($type)
				]);
			}
		}, new SimplePropertyTableExportFormater($DIC)),
		$factory->column("description", "Description", new SimplePropertyTableColumnFormater($DIC), new SimplePropertyTableExportFormater($DIC))
			->withDefaultSelected(false)->withSortable(false),
		$factory->actionColumn("actions", "Actions", [
			"Action" => $action_url
		])
	], new class($DIC) extends AbstractTableDataFetcher {

		/**
		 * @inheritDoc
		 */
		public function fetchData(TableFilter $filter, Factory $factory): TableDataInterface {
			$sql = 'SELECT *' . $this->getQuery($filter);

			$result = $this->dic->database()->query($sql);

			$rows = [];
			while (!empty($row = $this->dic->database()->fetchAssoc($result))) {
				$rows[] = $factory->rowData($row["obj_id"], (object)$row);
			}

			$sql = 'SELECT COUNT(obj_id) AS count' . $this->getQuery($filter, true);

			$result = $this->dic->database()->query($sql);

			$max_count = intval($result->fetchAssoc()["count"]);

			return new TableData($rows, $max_count);
		}


		/**
		 * @param TableFilter $filter
		 * @param bool        $max_count
		 *
		 * @return string
		 */
		protected function getQuery(TableFilter $filter, $max_count = false): string {
			$sql = ' FROM object_data';

			$field_values = array_filter($filter->getFieldValues());

			if (!empty($field_values)) {
				$sql .= ' WHERE ' . implode(' AND ', array_map(function (string $key, string $value): string {
						return $this->dic->database()->like($key, ilDBConstants::T_TEXT, '%' . $value . '%');
					}, array_keys($field_values), $field_values));
			}

			if (!$max_count) {
				if (!empty($filter->getSortFields())) {
					$sql .= ' ORDER BY ' . implode(", ", array_map(function (TableFilterSortField $sort_field): string {
							return $this->dic->database()->quoteIdentifier($sort_field->getSortField()) . ' ' . ($sort_field->getSortFieldDirection()
								=== TableFilterSortField::SORT_DIRECTION_DOWN ? 'DESC' : 'ASC');
						}, $filter->getSortFields()));
				}

				if (!empty($filter->getLimitStart()) && !empty($filter->getLimitEnd())) {
					$sql .= ' LIMIT ' . $this->dic->database()->quote($filter->getLimitStart(), ilDBConstants::T_INTEGER) . ','
						. $this->dic->database()->quote($filter->getLimitEnd(), ilDBConstants::T_INTEGER);
				}
			}

			return $sql;
		}
	}, new TableFilterStorage($DIC))->withFilterFields([
		"title" => $DIC->ui()->factory()->input()->field()->text("Title"),
		"type" => $DIC->ui()->factory()->input()->field()->text("Type")
	])->withExportFormats([
		$factory->exportFormatCSV(),
		$factory->exportFormatExcel(),
		$factory->exportFormatPDF()
	])->withMultipleActions([
		"Action" => $action_url
	]);

	$info_text = $DIC->ui()->factory()->legacy("");

	$action_row_id = $table->getActionRowId();
	if ($action_row_id !== "") {
		$info_text = $info_text = $DIC->ui()->factory()->messageBox()->info("Row id: " . $action_row_id);
	}

	$mutliple_action_row_ids = $table->getMultipleActionRowIds();
	if (!empty($mutliple_action_row_ids)) {
		$info_text = $DIC->ui()->factory()->messageBox()->info("Row ids: " . implode(", ", $mutliple_action_row_ids));
	}

	return $DIC->ui()->renderer()->render([ $info_text, $table ]);
}
