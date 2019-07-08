<?php

namespace ILIAS\UI\Component\Table\Data\Column\Formater;

use ILIAS\DI\Container;
use ILIAS\UI\Component\Table\Data\Column\TableColumn;
use ILIAS\UI\Component\Table\Data\Data\Row\TableRowData;
use ILIAS\UI\Renderer;

/**
 * Interface TableColumnFormater
 *
 * @package ILIAS\UI\Component\Table\Data\Column\Formater
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface TableColumnFormater {

	/**
	 * TableColumnFormater constructor
	 *
	 * @param Container $dic
	 */
	public function __construct(Container $dic);


	/**
	 * @param TableColumn $column
	 * @param string      $table_id
	 * @param Renderer    $renderer
	 *
	 * @return string
	 */
	public function formatHeader(TableColumn $column, string $table_id, Renderer $renderer): string;


	/**
	 * @param TableColumn  $column
	 * @param TableRowData $row
	 * @param string       $table_id
	 * @param Renderer     $renderer
	 *
	 * @return string
	 */
	public function formatRow(TableColumn $column, TableRowData $row, string $table_id, Renderer $renderer): string;
}
