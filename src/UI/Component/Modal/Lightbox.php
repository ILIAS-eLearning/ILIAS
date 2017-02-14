<?php

namespace ILIAS\UI\Component\Modal;

use ILIAS\UI\Component\Button;

/**
 * Interface Lightbox
 *
 * @package ILIAS\UI\Component\Modal
 */
interface Lightbox extends Modal
{

	/**
	 * Get a modal like this with the given lightbox pages.
	 *
	 * @param LightboxPage[] $pages
	 *
	 * @return Lightbox
	 */
	public function withPages(array $pages);


	/**
	 * Get the lightbox pages of this modal
	 *
	 * @return LightboxPage[]
	 */
	public function getPages();
}
