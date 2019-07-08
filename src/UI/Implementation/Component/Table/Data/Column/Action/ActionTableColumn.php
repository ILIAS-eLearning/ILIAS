<?php

namespace ILIAS\UI\Implementation\Component\Table\Data\Column\Action;

use ILIAS\UI\Component\Table\Data\Column\Action\ActionTableColumn as ActionTableColumnInterface;
use ILIAS\UI\Component\Table\Data\Column\TableColumn as TableColumnInterface;
use ILIAS\UI\Implementation\Component\Table\Data\Column\TableColumn;

/**
 * Class ActionTableColumn
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data\Column\Action
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class ActionTableColumn extends TableColumn implements ActionTableColumnInterface {

	/**
	 * @var string[]
	 */
	protected $actions = [];


	/**
	 * @inheritDoc
	 */
	public function getActions(): array {
		return $this->actions;
	}


	/**
	 * @inheritDoc
	 */
	public function withActions(array $actions): TableColumnInterface {
		$clone = clone $this;

		$clone->actions = $actions;

		return $clone;
	}
}
