<?php namespace ILIAS\GlobalScreen\Scope\View;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Layout\Page\Page;

/**
 * Interface View
 *
 * @package ILIAS\GlobalScreen\Scope\View
 */
interface View {

	/**
	 * @param bool $bool
	 *
	 * @return View
	 */
	public function usesNavigation(bool $bool): View;


	/**
	 * @return bool
	 */
	public function hasNavigation(): bool;


	/**
	 * @param Component $content
	 *
	 * @return View
	 */
	public function addContent(Component $content): View;


	/**
	 * @return Component[]
	 */
	public function getContent(): array;


	/**
	 * @return Page
	 */
	public function getPageForViewWithContent(): Page;
}
