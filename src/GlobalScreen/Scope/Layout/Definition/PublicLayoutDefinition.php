<?php namespace ILIAS\GlobalScreen\Scope\Layout\Definition;

/**
 * Class PublicLayoutDefinition
 *
 * @package ILIAS\GlobalScreen\Scope\LayoutDefinition
 */
class PublicLayoutDefinition extends AbstractLayoutDefinition implements LayoutDefinition {

	/**
	 * @inheritDoc
	 */
	public function hasMainBar(): bool {
		return false;
	}


	/**
	 * @inheritDoc
	 */
	public function hasMetaBar(): bool {
		return false;
	}


	/**
	 * @inheritDoc
	 */
	public function hasBreadCrumbs(): bool {
		return false;
	}
}
