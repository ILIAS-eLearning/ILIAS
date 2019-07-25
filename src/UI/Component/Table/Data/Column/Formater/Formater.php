<?php

namespace ILIAS\UI\Component\Table\Data\Column\Formater;

use ILIAS\DI\Container;
use ILIAS\UI\Component\Table\Data\Column\Column;
use ILIAS\UI\Component\Table\Data\Data\Row\RowData;
use ILIAS\UI\Component\Table\Data\Format\Format;
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
	 * @param Format   $format
	 * @param Column   $column
	 * @param string   $table_id
	 * @param Renderer $renderer
	 *
	 * @return string
	 */
	public function formatHeaderCell(Format $format, Column $column, string $table_id, Renderer $renderer): string;


	/**
	 * @param Format   $format
	 * @param Column   $column
	 * @param RowData  $row
	 * @param mixed    $value
	 * @param string   $table_id
	 * @param Renderer $renderer
	 *
	 * @return string
	 */
	public function formatRowCell(Format $format, Column $column, RowData $row, $value, string $table_id, Renderer $renderer): string;
}
