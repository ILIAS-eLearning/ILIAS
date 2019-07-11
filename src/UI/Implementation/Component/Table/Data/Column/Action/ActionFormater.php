<?php

namespace ILIAS\UI\Implementation\Component\Table\Data\Column\Action;

use ILIAS\UI\Component\Button\Shy;
use ILIAS\UI\Component\Table\Data\Column\Action\ActionColumn;
use ILIAS\UI\Component\Table\Data\Column\Column;
use ILIAS\UI\Component\Table\Data\Data\Row\RowData;
use ILIAS\UI\Component\Table\Data\Table;
use ILIAS\UI\Implementation\Component\Table\Data\Column\Formater\AbstractFormater;
use ILIAS\UI\Implementation\Component\Table\Data\Renderer;
use ILIAS\UI\Renderer as RendererInterface;

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
	public function formatHeader(string $format_id, Column $column, string $table_id, RendererInterface $renderer): string {
		return $column->getTitle();
	}


	/**
	 * @inheritDoc
	 *
	 * @param ActionColumn $column
	 */
	public function formatRow(string $format_id, Column $column, RowData $row, string $table_id, RendererInterface $renderer): string {
		return $renderer->render($this->dic->ui()->factory()->dropdown()
			->standard(array_map(function (string $title, string $action) use ($row, $table_id): Shy {
				return $this->dic->ui()->factory()->button()
					->shy($title, Renderer::getActionUrl($action, [ Table::ACTION_GET_VAR => $row->getRowId() ], $table_id));
			}, array_keys($column->getActions()), $column->getActions()))->withLabel($column->getTitle()));
	}
}
