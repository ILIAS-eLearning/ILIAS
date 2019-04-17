<?php namespace ILIAS\GlobalScreen\Scope\Layout\Definition;

use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;
use ILIAS\UI\Component\MainControls\MainBar;
use ILIAS\UI\Component\MainControls\MetaBar;

/**
 * Interface LayoutDefinition
 *
 * @package ILIAS\GlobalScreen\Scope\LayoutDefinition
 */
interface LayoutDefinition {

	//
	// MainControls and Navigation
	//

	/**
	 * @param bool $bool
	 *
	 * @return LayoutDefinition
	 */
	// public function usesMainBar(bool $bool): LayoutDefinition;

	/**
	 * @return bool
	 */
	public function hasMainBar(): bool;


	/**
	 * @return MainBar
	 */
	// public function getMainBar(): MainBar;

	/**
	 * @return bool
	 */
	// public function usesMetaBar(bool $bool): LayoutDefinition;

	/**
	 * @return bool
	 */
	public function hasMetaBar(): bool;


	/**
	 * @return MetaBar
	 */
	// public function getMetaBar(): MetaBar;

	/**
	 * @param bool $bool
	 *
	 * @return LayoutDefinition
	 */
	// public function usesBreadCrumbs(bool $bool): LayoutDefinition;

	/**
	 * @return bool
	 */
	public function hasBreadCrumbs(): bool;


	/**
	 * @return Breadcrumbs
	 */
	// public function getBreadCrumbs(): Breadcrumbs;
}
