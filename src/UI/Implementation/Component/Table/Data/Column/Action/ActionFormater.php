<?php

namespace ILIAS\UI\Implementation\Component\Table\Data\Column\Action;

use ILIAS\UI\Component\Button\Shy;
use ILIAS\UI\Component\Table\Data\Column\Action\ActionColumn;
use ILIAS\UI\Component\Table\Data\Column\Column;
use ILIAS\UI\Component\Table\Data\Data\Row\RowData;
use ILIAS\UI\Component\Table\Data\Format\Format;
use ILIAS\UI\Component\Table\Data\Table;
use ILIAS\UI\Implementation\Component\Table\Data\Column\Formater\AbstractFormater;
use ILIAS\UI\Implementation\Component\Table\Data\Format\DefaultBrowserFormat;
use ILIAS\UI\Renderer;

/**
 * Class ActionFormater
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data\Column\Action
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class ActionFormater extends AbstractFormater {

	/**
	 * @inheritDoc
	 */
	public function formatHeaderCell(Format $format, Column $column, string $table_id, Renderer $renderer): string {
		return $column->getTitle();
	}


	/**
	 * @inheritDoc
	 *
	 * @param ActionColumn $column
	 */
	public function formatRowCell(Format $format, $value, Column $column, RowData $row, string $table_id, Renderer $renderer): string {
		$actions = $column->getActions($row);

		return $renderer->render($this->dic->ui()->factory()->dropdown()
			->standard(array_map(function (string $title, string $action) use ($row, $table_id): Shy {
				return $this->dic->ui()->factory()->button()
					->shy($title, DefaultBrowserFormat::getActionUrl($action, [ Table::ACTION_GET_VAR => $row->getRowId() ], $table_id));
			}, array_keys($actions), $actions))->withLabel($column->getTitle()));
	}
}
