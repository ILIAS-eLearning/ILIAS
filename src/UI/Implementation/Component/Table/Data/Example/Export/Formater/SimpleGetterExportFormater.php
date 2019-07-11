<?php

namespace ILIAS\UI\Implementation\Component\Table\Data\Column\Formater;

use ILIAS\UI\Component\Table\Data\Column\Column;
use ILIAS\UI\Component\Table\Data\Data\Row\RowData;
use ILIAS\UI\Component\Table\Data\Export\ExportFormat;
use ILIAS\UI\Implementation\Component\Table\Data\Export\Formater\AbstractExportFormater;
use ILIAS\UI\Renderer;
use Throwable;

/**
 * Class SimpleGetterExportFormater
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data\Column\Formater
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class SimpleGetterExportFormater extends AbstractExportFormater {

	/**
	 * @inheritDoc
	 */
	public function formatHeader(ExportFormat $export_format, Column $column, Renderer $renderer, string $table_id): string {
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
	public function formatRow(ExportFormat $export_format, Column $column, RowData $row, Renderer $renderer, string $table_id): string {
		$value = "";

		switch ($export_format->getExportId()) {
			default:
				try {
					if (method_exists($row->getOriginalData(), $method = "get" . $this->strToCamelCase($column->getKey()))) {
						$value = strval($row->getOriginalData()->{$method}());
					}

					if (method_exists($row->getOriginalData(), $method = "is" . $this->strToCamelCase($column->getKey()))) {
						$value = strval($row->getOriginalData()->{$method}());
					}
				} catch (Throwable $ex) {
					$value = "";
				}
				break;
		}

		return strval($value);
	}
}
