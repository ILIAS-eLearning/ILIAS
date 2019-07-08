<?php

namespace ILIAS\UI\Component\Table\Data\Export\Formater;

use ILIAS\DI\Container;
use ILIAS\UI\Component\Table\Data\Column\TableColumn;
use ILIAS\UI\Component\Table\Data\Data\Row\TableRowData;
use ILIAS\UI\Component\Table\Data\Export\TableExportFormat;
use ILIAS\UI\Renderer;

/**
 * Interface TableExportFormater
 *
 * @package ILIAS\UI\Component\Table\Data\Export\Formater
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface TableExportFormater {

	/**
	 * TableExportFormater constructor
	 *
	 * @param Container $dic
	 */
	public function __construct(Container $dic);


	/**
	 * @param TableExportFormat $export_format
	 * @param TableColumn       $column
	 * @param string            $table_id
	 * @param Renderer          $renderer
	 *
	 * @return string
	 */
	public function formatHeader(TableExportFormat $export_format, TableColumn $column, string $table_id, Renderer $renderer): string;


	/**
	 * @param TableExportFormat $export_format
	 * @param TableColumn       $column
	 * @param TableRowData      $row
	 * @param string            $table_id
	 * @param Renderer          $renderer
	 *
	 * @return string
	 */
	public function formatRow(TableExportFormat $export_format, TableColumn $column, TableRowData $row, string $table_id, Renderer $renderer): string;
}
