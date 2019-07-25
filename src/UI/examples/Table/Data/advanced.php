<?php

use ILIAS\UI\Component\Table\Data\Column\Column;
use ILIAS\UI\Component\Table\Data\Data\Data;
use ILIAS\UI\Component\Table\Data\Data\Row\RowData;
use ILIAS\UI\Component\Table\Data\UserTableSettings\Settings;
use ILIAS\UI\Component\Table\Data\UserTableSettings\Sort\SortField;
use ILIAS\UI\Component\Table\Data\Format\Format;
use ILIAS\UI\Implementation\Component\Table\Data\Column\Formater\DefaultFormater;
use ILIAS\UI\Implementation\Component\Table\Data\Data\Fetcher\AbstractDataFetcher;
use ILIAS\UI\Renderer;

/**
 * @return string
 */
function advanced(): string {
	global $DIC;

	$action_url = $DIC->ctrl()->getLinkTargetByClass(ilSystemStyleDocumentationGUI::class);

	$factory = $DIC->ui()->factory()->table()->data($DIC);

	$table = $factory->table("example_datatable_advanced", $action_url, "Advanced example data table", [
		$factory->column("obj_id", "Id")->withDefaultSelected(false),
		$factory->column("title", "Title")->withDefaultSort(true),
		$factory->column("type", "Type")->withFormater(new class($DIC) extends DefaultFormater {

			/**
			 * @inheritDoc
			 */
			public function formatRowCell(Format $format, $value, Column $column, RowData $row, string $table_id, Renderer $renderer): string {
				$type = parent::formatRowCell($format, $value, $column, $row, $table_id, $renderer);

				switch ($format->getFormatId()) {
					case Format::FORMAT_BROWSER:
					case Format::FORMAT_PDF:
					case Format::FORMAT_HTML:
						return $renderer->render([
							$this->dic->ui()->factory()->symbol()->icon()->custom(ilObject::_getIcon($row->getRowId(), "small"), $type),
							$this->dic->ui()->factory()->legacy($type)
						]);

					default:
						return $type;
				}
			}
		}),
		$factory->column("description", "Description")->withDefaultSelected(false)->withSortable(false),
		$factory->actionColumn("actions", "Actions", [
			"Action" => $action_url
		])
	], new class($DIC) extends AbstractDataFetcher {

		/**
		 * @inheritDoc
		 */
		public function fetchData(Settings $user_table_settings): Data {
			$sql = 'SELECT *' . $this->getQuery($user_table_settings);

			$result = $this->dic->database()->query($sql);

			$rows = [];
			while (!empty($row = $this->dic->database()->fetchObject($result))) {
				$rows[] = $this->propertyRowData($row->obj_id, $row);
			}

			$sql = 'SELECT COUNT(obj_id) AS count' . $this->getQuery($user_table_settings, true);

			$result = $this->dic->database()->query($sql);

			$max_count = intval($result->fetchAssoc()["count"]);

			return $this->data($rows, $max_count);
		}


		/**
		 * @param Settings $user_table_settings
		 * @param bool     $max_count
		 *
		 * @return string
		 */
		protected function getQuery(Settings $user_table_settings, $max_count = false): string {
			$sql = ' FROM object_data';

			$field_values = array_filter($user_table_settings->getFieldValues());

			if (!empty($field_values)) {
				$sql .= ' WHERE ' . implode(' AND ', array_map(function (string $key, string $value): string {
						return $this->dic->database()->like($key, ilDBConstants::T_TEXT, '%' . $value . '%');
					}, array_keys($field_values), $field_values));
			}

			if (!$max_count) {
				if (!empty($user_table_settings->getSortFields())) {
					$sql .= ' ORDER BY ' . implode(", ", array_map(function (SortField $sort_field): string {
							return $this->dic->database()->quoteIdentifier($sort_field->getSortField()) . ' ' . ($sort_field->getSortFieldDirection()
								=== SortField::SORT_DIRECTION_DOWN ? 'DESC' : 'ASC');
						}, $user_table_settings->getSortFields()));
				}

				if (!empty($user_table_settings->getLimitStart()) && !empty($user_table_settings->getLimitEnd())) {
					$sql .= ' LIMIT ' . $this->dic->database()->quote($user_table_settings->getLimitStart(), ilDBConstants::T_INTEGER) . ','
						. $this->dic->database()->quote($user_table_settings->getLimitEnd(), ilDBConstants::T_INTEGER);
				}
			}

			return $sql;
		}
	})->withFilterFields([
		"title" => $DIC->ui()->factory()->input()->field()->text("Title"),
		"type" => $DIC->ui()->factory()->input()->field()->text("Type")
	])->withFormats([
		$factory->formatCSV(),
		$factory->formatExcel(),
		$factory->formatPDF(),
		$factory->formatHTML()
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
