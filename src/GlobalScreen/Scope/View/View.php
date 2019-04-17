<?php namespace ILIAS\GlobalScreen\Scope\View;

use ILIAS\GlobalScreen\Scope\View\MetaContent\MetaContent;
use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Layout\Page\Page;
use ILIAS\UI\Component\MainControls\MainBar;
use ILIAS\UI\Component\MainControls\MetaBar;

/**
 * Interface View
 *
 * @package ILIAS\GlobalScreen\Scope\View
 */
interface View {

	//
	// MetaContent such as JS and CSS
	//

	/**
	 * @return MetaContent
	 */
	public function metaContent(): MetaContent;


	//
	// MainControls and Navigation
	//

	/**
	 * @param bool $bool
	 *
	 * @return View
	 */
	public function usesMainBar(bool $bool): View;


	/**
	 * @return bool
	 */
	public function hasMainBar(): bool;


	/**
	 * @return MainBar
	 */
	public function getMainBar(): MainBar;


	/**
	 * @return bool
	 */
	public function usesMetaBar(bool $bool): View;


	/**
	 * @return bool
	 */
	public function hasMetaBar(): bool;


	/**
	 * @return MetaBar
	 */
	public function getMetaBar(): MetaBar;


	/**
	 * @param bool $bool
	 *
	 * @return View
	 */
	public function usesBreadCrumbs(bool $bool): View;


	/**
	 * @return bool
	 */
	public function hasBreadCrumbs(): bool;


	/**
	 * @return Breadcrumbs
	 */
	public function getBreadCrumbs(): Breadcrumbs;


	/**
	 * @param Component $content
	 *
	 * @return View
	 */
	public function setContent(Component $content): View;


	/**
	 * @return Component[]
	 */
	public function getContent(): array;


	/**
	 * @return Page
	 */
	public function getPageForViewWithContent(): Page;
}
