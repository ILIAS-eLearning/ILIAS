<?php

namespace ILIAS\UI\Implementation\Component\Table\Data\Column\Formater;

use ILIAS\UI\Component\Table\Data\Column\Column;
use ILIAS\UI\Component\Table\Data\Data\Row\RowData;
use ILIAS\UI\Component\Table\Data\Format\Format;
use ILIAS\UI\Renderer;

/**
 * Class DefaultFormater
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data\Column\Formater
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class DefaultFormater extends AbstractFormater {

	/**
	 * @inheritDoc
	 */
	public function formatHeader(string $format_id, Column $column, string $table_id, Renderer $renderer): string {
		$title = $column->getTitle();

		switch ($format_id) {
			case Format::FORMAT_PDF:
				return "<b>{$title}</b>";

			default:
				return $title;
		}
	}


	/**
	 * @inheritDoc
	 */
	public function formatRow(string $format_id, Column $column, RowData $row, string $table_id, Renderer $renderer): string {
		$value = $row($column->getKey());

		$value = strval($value);

		switch ($format_id) {
			default:
				return $value;
		}
	}
}
