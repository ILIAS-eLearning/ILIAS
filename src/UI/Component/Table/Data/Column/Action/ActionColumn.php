<?php

namespace ILIAS\UI\Component\Table\Data\Column\Action;

use ILIAS\UI\Component\Table\Data\Column\Column;

/**
 * Interface ActionColumn
 *
 * @package ILIAS\UI\Component\Table\Data\Column\Action
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface ActionColumn extends Column {

	/* TODO: Needs PHP 7.3
	/**
	 * @inheritDoc
	 *
	 * @param array $actions
	 * /
	public function __construct(string $key, string $title, array $actions);
	*/

	/**
	 * @return string[]
	 */
	public function getActions(): array;


	/**
	 * @param string[] $actions
	 *
	 * @return Column
	 */
	public function withActions(array $actions): Column;
}
