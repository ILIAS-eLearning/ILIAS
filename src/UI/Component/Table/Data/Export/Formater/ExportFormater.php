<?php

namespace ILIAS\UI\Component\Table\Data\Export\Formater;

use ILIAS\DI\Container;
use ILIAS\UI\Component\Table\Data\Column\Column;
use ILIAS\UI\Component\Table\Data\Data\Row\RowData;
use ILIAS\UI\Component\Table\Data\Export\ExportFormat;
use ILIAS\UI\Renderer;

/**
 * Interface ExportFormater
 *
 * @package ILIAS\UI\Component\Table\Data\Export\Formater
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface ExportFormater {

	/**
	 * ExportFormater constructor
	 *
	 * @param Container $dic
	 */
	public function __construct(Container $dic);


	/**
	 * @param ExportFormat $export_format
	 * @param Column       $column
	 * @param string       $table_id
	 * @param Renderer     $renderer
	 *
	 * @return string
	 */
	public function formatHeader(ExportFormat $export_format, Column $column, string $table_id, Renderer $renderer): string;


	/**
	 * @param ExportFormat $export_format
	 * @param Column       $column
	 * @param RowData      $row
	 * @param string       $table_id
	 * @param Renderer     $renderer
	 *
	 * @return string
	 */
	public function formatRow(ExportFormat $export_format, Column $column, RowData $row, string $table_id, Renderer $renderer): string;
}
