<?php

namespace ILIAS\UI\Implementation\Component\Table\Data\Column\Formater;

use ILIAS\UI\Component\Table\Data\Column\Column;
use ILIAS\UI\Component\Table\Data\Data\Row\RowData;
use ILIAS\UI\Implementation\Component\Table\Data\Export\Formater\AbstractColumnFormater;
use ILIAS\UI\Renderer;

/**
 * Class SimplePropertyColumnFormater
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data\Column\Formater
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class SimplePropertyColumnFormater extends AbstractColumnFormater {

	/**
	 * @inheritDoc
	 */
	public function formatHeader(Column $column, string $table_id, Renderer $renderer): string {
		return $column->getTitle();
	}


	/**
	 * @inheritDoc
	 */
	public function formatRow(Column $column, RowData $row, string $table_id, Renderer $renderer): string {
		$value = $row->getOriginalData()->{$column->getKey()};

		return strval($value);
	}
}
