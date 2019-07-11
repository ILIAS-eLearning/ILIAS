<?php

namespace ILIAS\UI\Implementation\Component\Table\Data\Column\Action;

use ILIAS\UI\Component\Table\Data\Column\Action\ActionColumn as ActionColumnInterface;
use ILIAS\UI\Component\Table\Data\Column\Column as ColumnInterface;
use ILIAS\UI\Implementation\Component\Table\Data\Column\Column;

/**
 * Class ActionColumn
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data\Column\Action
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class ActionColumn extends Column implements ActionColumnInterface {

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
	public function withActions(array $actions): ColumnInterface {
		$clone = clone $this;

		$clone->actions = $actions;

		return $clone;
	}
}
