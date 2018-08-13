<?php
namespace ILIAS\UI\Component\Modal;

/**
 * Interface LightboxPage
 *
 * A lightbox descriptive page represents a page displaying a media element, such as image or video.
 */
interface LightboxDescriptionEnabledPage extends LightboxPage {
	/**
	 * Get the description of this page, displayed along with the media item
	 *
	 * @return string
	 */
	public function getDescription(): string;
}