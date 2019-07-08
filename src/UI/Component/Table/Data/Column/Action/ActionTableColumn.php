<?php

namespace ILIAS\UI\Component\Table\Data\Column\Action;

use ILIAS\UI\Component\Table\Data\Column\TableColumn;

/**
 * Interface ActionTableColumn
 *
 * @package ILIAS\UI\Component\Table\Data\Column\Action
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface ActionTableColumn extends TableColumn {

	/**
	 * @return string[]
	 */
	public function getActions(): array;


	/**
	 * @param string[] $actions
	 *
	 * @return TableColumn
	 */
	public function withActions(array $actions): TableColumn;
}
