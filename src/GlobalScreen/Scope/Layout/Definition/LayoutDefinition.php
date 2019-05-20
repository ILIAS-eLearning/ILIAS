<?php namespace ILIAS\GlobalScreen\Scope\Layout\Definition;

/**
 * Interface LayoutDefinition
 *
 * @package ILIAS\GlobalScreen\Scope\LayoutDefinition
 */
interface LayoutDefinition {

	/**
	 * @return bool
	 */
	public function hasMainBar(): bool;


	/**
	 * @return bool
	 */
	public function hasMetaBar(): bool;


	/**
	 * @return bool
	 */
	public function hasBreadCrumbs(): bool;
}
