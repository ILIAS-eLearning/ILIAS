<?php

namespace ILIAS\UI\Component\Modal;

use ILIAS\UI\Component\Component;

/**
 * Interface LightboxPage
 *
 * A lightbox page represents a page displaying a media element, such as image or video.
 */
interface LightboxPage {

	/**
	 * Get the title of this page, displayed as title in the lightbox modal.
	 *
	 * @return string
	 */
	public function getTitle();


	/**
	 * Get the description of this page, displayed along with the media item
	 *
	 * @return string
	 */
	public function getDescription();


	/**
	 * Get the component representing the media item to be displayed in the modals
	 * content section, e.g. an image.
	 *
	 * @return Component
	 */
	public function getComponent();
}