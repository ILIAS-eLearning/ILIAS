<?php

namespace ILIAS\UI\Implementation\Component\Table\Data\Format;

use ilCSVWriter;
use ILIAS\UI\Renderer;

/**
 * Class CSVFormat
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data\Format
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class CSVFormat extends AbstractFormat {

	/**
	 * @inheritDoc
	 */
	public function getFormatId(): string {
		return self::FORMAT_CSV;
	}


	/**
	 * @inheritDoc
	 */
	public function getFileExtension(): string {
		return "csv";
	}


	/**
	 * @inheritDoc
	 */
	public function render(array $columns, array $rows, string $title, string $table_id, Renderer $renderer): string {
		$csv = new ilCSVWriter();

		$csv->setSeparator(";");

		foreach ($columns as $column) {
			$csv->addColumn($column);
		}
		$csv->addRow();

		foreach ($rows as $row) {
			foreach ($row as $column) {
				$csv->addColumn($column);
			}
			$csv->addRow();
		}

		return $csv->getCSVString();
	}
}
