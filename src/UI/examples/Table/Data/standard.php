<?php

use ILIAS\UI\Component\Table\Data\Data\Row\TableRowData;
use ILIAS\UI\Component\Table\Data\Data\TableData;
use ILIAS\UI\Component\Table\Data\Factory\Factory;
use ILIAS\UI\Component\Table\Data\Filter\Sort\TableFilterSortField;
use ILIAS\UI\Component\Table\Data\Filter\TableFilter;
use ILIAS\UI\Implementation\Component\Table\Data\Column\Formater\SimplePropertyTableColumnFormater;
use ILIAS\UI\Implementation\Component\Table\Data\Column\Formater\SimplePropertyTableExportFormater;
use ILIAS\UI\Implementation\Component\Table\Data\Data\Fetcher\AbstractTableDataFetcher;
use ILIAS\UI\Implementation\Component\Table\Data\Filter\Storage\TableFilterStorage;

/**
 * @return string
 */
function standard(): string {
	global $DIC;

	$action_url = $DIC->ctrl()->getLinkTargetByClass(ilSystemStyleDocumentationGUI::class) . "&node_id=TableDataData";

	$factory = $DIC->ui()->factory()->table()->data($dic);

	$table = $factory->table("example_datatable_actions", $action_url, "Example data table with actions", [
		$factory->column("column1", "Column 1", new SimplePropertyTableColumnFormater($DIC), new SimplePropertyTableExportFormater($DIC)),
		$factory->column("column2", "Column 2", new SimplePropertyTableColumnFormater($DIC), new SimplePropertyTableExportFormater($DIC)),
		$factory->column("column3", "Column 3", new SimplePropertyTableColumnFormater($DIC), new SimplePropertyTableExportFormater($DIC))
	], new class($DIC) extends AbstractTableDataFetcher {

		/**
		 * @inheritDoc
		 */
		public function fetchData(TableFilter $filter, Factory $factory): TableData {
			$data = array_map(function (int $index): stdClass {
				return (object)[
					"column1" => $index,
					"column2" => "text $index",
					"column3" => ($index % 2 === 0 ? "true" : "false")
				];
			}, range(0, 25));

			$data = array_filter($data, function (stdClass $data) use ($filter): bool {
				$match = true;

				foreach ($filter->getFieldValues() as $key => $value) {
					if (!empty($value)) {
						switch (true) {
							case is_array($value):
								$match = in_array($data->{$key}, $value);
								break;

							case is_integer($data->{$key}):
							case is_float($data->{$key}):
								$match = ($data->{$key} === intval($value));
								break;

							case is_string($data->{$key}):
								$match = (stripos($data->{$key}, $value) !== false);
								break;

							default:
								$match = ($data->{$key} === $value);
								break;
						}

						if (!$match) {
							break;
						}
					}
				}

				return $match;
			});

			usort($data, function (stdClass $o1, stdClass $o2) use ($filter): int {
				foreach ($filter->getSortFields() as $sort_field) {
					$s1 = strval($o1->{$sort_field->getSortField()});
					$s2 = strval($o2->{$sort_field->getSortField()});

					$i = strnatcmp($s1, $s2);

					if ($sort_field->getSortFieldDirection() === TableFilterSortField::SORT_DIRECTION_DOWN) {
						$i *= - 1;
					}

					if ($i !== 0) {
						return $i;
					}
				}

				return 0;
			});

			$max_count = count($data);

			$data = array_slice($data, $filter->getLimitStart(), $filter->getLimitEnd());

			$data = array_map(function (stdClass $row) use ($factory): TableRowData {
				return $factory->rowData($row->column1, $row);
			}, $data);

			return $factory->data($data, $max_count);
		}
	}, new TableFilterStorage($DIC));

	return $DIC->ui()->renderer()->render($table);
}
