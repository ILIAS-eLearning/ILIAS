<?php

namespace ILIAS\UI\Component\Table\Data\Column\Formater;

use ILIAS\DI\Container;
use ILIAS\UI\Component\Table\Data\Column\Column;
use ILIAS\UI\Component\Table\Data\Data\Row\RowData;
use ILIAS\UI\Renderer;

/**
 * Interface Formater
 *
 * @package ILIAS\UI\Component\Table\Data\Column\Formater
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface Formater {

	/**
	 * Formater constructor
	 *
	 * @param Container $dic
	 */
	public function __construct(Container $dic);


	/**
	 * @param string   $format_id
	 * @param Column   $column
	 * @param string   $table_id
	 * @param Renderer $renderer
	 *
	 * @return string
	 */
	public function formatHeader(string $format_id, Column $column, string $table_id, Renderer $renderer): string;


	/**
	 * @param string   $format_id
	 * @param Column   $column
	 * @param RowData  $row
	 * @param string   $table_id
	 * @param Renderer $renderer
	 *
	 * @return string
	 */
	public function formatRow(string $format_id, Column $column, RowData $row, string $table_id, Renderer $renderer): string;
}
