<?php namespace ILIAS\GlobalScreen\Scope\Layout\Definition;

/**
 * Class StandardLayoutDefinition
 *
 * @package ILIAS\GlobalScreen\Scope\LayoutDefinition
 */
class StandardLayoutDefinition implements LayoutDefinition {

	/**
	 * @inheritDoc
	 */
	public function hasMainBar(): bool {
		return true;
	}


	/**
	 * @inheritDoc
	 */
	public function hasMetaBar(): bool {
		return true;
	}


	/**
	 * @inheritDoc
	 */
	public function hasBreadCrumbs(): bool {
		return true;
	}
}
