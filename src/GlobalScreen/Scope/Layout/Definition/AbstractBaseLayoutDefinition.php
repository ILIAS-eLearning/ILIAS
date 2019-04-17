<?php namespace ILIAS\GlobalScreen\Scope\Layout\Definition;

/**
 * Class AbstractBaseLayoutDefinition
 *
 * @package ILIAS\GlobalScreen\Scope\LayoutDefinition
 */
abstract class AbstractBaseLayoutDefinition implements LayoutDefinition {

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
