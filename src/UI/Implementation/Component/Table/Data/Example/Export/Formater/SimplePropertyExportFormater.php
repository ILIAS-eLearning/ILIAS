<?php

namespace ILIAS\UI\Implementation\Component\Table\Data\Column\Formater;

use ILIAS\UI\Component\Table\Data\Column\Column;
use ILIAS\UI\Component\Table\Data\Data\Row\RowData;
use ILIAS\UI\Component\Table\Data\Export\ExportFormat;
use ILIAS\UI\Implementation\Component\Table\Data\Export\Formater\AbstractExportFormater;
use ILIAS\UI\Renderer;
use Throwable;

/**
 * Class SimplePropertyExportFormater
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data\Column\Formater
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class SimplePropertyExportFormater extends AbstractExportFormater {

	/**
	 * @inheritDoc
	 */
	public function formatHeader(ExportFormat $export_format, Column $column, string $table_id, Renderer $renderer): string {
		$value = "";

		switch ($export_format->getExportId()) {
			case ExportFormat::EXPORT_FORMAT_PDF:
				$value = "<b>{$column->getTitle()}</b>";
				break;

			default:
				$value = $column->getTitle();
				break;
		}

		return strval($value);
	}


	/**
	 * @inheritDoc
	 */
	public function formatRow(ExportFormat $export_format, Column $column, RowData $row, string $table_id, Renderer $renderer): string {
		$value = "";

		switch ($export_format->getExportId()) {
			default:
				try {
					$value = strval($row->getOriginalData()->{$column->getKey()});
				} catch (Throwable $ex) {
					$value = "";
				}
				break;
		}

		return strval($value);
	}
}
