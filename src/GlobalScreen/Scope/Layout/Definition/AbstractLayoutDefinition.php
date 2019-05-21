<?php namespace ILIAS\GlobalScreen\Scope\Layout\Definition;

/**
 * Class AbstractLayoutDefinition
 *
 * @package ILIAS\GlobalScreen\Scope\LayoutDefinition
 */
abstract class AbstractLayoutDefinition implements LayoutDefinition {

	/**
	 * @inheritDoc
	 */
	public function hasFooter(): bool {
		return false;
	}


	/**
	 * @inheritDoc
	 */
	public function hasLeaveFunction(): bool {
		return false;
	}
}
